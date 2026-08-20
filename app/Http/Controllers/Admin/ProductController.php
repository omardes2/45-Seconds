<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\CurrencyEnum;
use App\Enums\MediaType;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Product;
use App\Services\AuditLogger;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly MediaService $media,
        private readonly AuditLogger $audit,
    ) {}

    public function index(): View
    {
        $products = Product::withCount('landingPages')->latest()->paginate(15);

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        return view('admin.products.create', $this->formData());
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $product = DB::transaction(function () use ($request, $data) {
            $product = new Product([
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($data['slug'] ?? $data['name']),
                'sku' => $data['sku'] ?? null,
                'description' => $data['description'] ?? null,
                'base_price' => $data['base_price'],
                'compare_at_price' => $data['compare_at_price'] ?? null,
                'currency' => $data['currency'],
                'status' => $data['status'],
            ]);

            if ($request->hasFile('main_image')) {
                $product->main_image = $this->media->storeImage($request->file('main_image'), 'products');
            }

            $product->save();
            $this->syncMedia($request, $product);

            return $product;
        });

        $this->audit->log(AuditAction::Created, $product, ['name' => $product->name]);

        return redirect()->route('admin.products.edit', $product)->with('success', 'تم إنشاء المنتج.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', array_merge(
            $this->formData(),
            ['product' => $product->load('media')],
        ));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $product, $data) {
            $product->fill([
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($data['slug'] ?? $data['name'], $product->id),
                'sku' => $data['sku'] ?? null,
                'description' => $data['description'] ?? null,
                'base_price' => $data['base_price'],
                'compare_at_price' => $data['compare_at_price'] ?? null,
                'currency' => $data['currency'],
                'status' => $data['status'],
            ]);

            if ($request->hasFile('main_image')) {
                $this->media->delete($product->main_image);
                $product->main_image = $this->media->storeImage($request->file('main_image'), 'products');
            }

            $product->save();
            $this->syncMedia($request, $product);
        });

        $this->audit->log(AuditAction::Updated, $product, ['name' => $product->name]);

        return redirect()->route('admin.products.edit', $product)->with('success', 'تم تحديث المنتج.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->landingPages()->exists()) {
            return back()->with('error', 'لا يمكن حذف منتج مرتبط بصفحات بيع.');
        }

        $this->audit->log(AuditAction::Archived, $product, ['name' => $product->name]);
        $product->delete(); // soft delete

        return redirect()->route('admin.products.index')->with('success', 'تم أرشفة المنتج.');
    }

    public function destroyMedia(Product $product, int $mediaId): RedirectResponse
    {
        $media = $product->media()->findOrFail($mediaId);
        $this->media->delete($media->path);
        $media->delete();

        return back()->with('success', 'تم حذف الوسيط.');
    }

    /**
     * Persist newly uploaded gallery images and an optional video URL.
     */
    private function syncMedia(StoreProductRequest|UpdateProductRequest $request, Product $product): void
    {
        $sort = (int) $product->media()->max('sort_order');

        foreach ((array) $request->file('gallery', []) as $image) {
            $path = $this->media->storeImage($image, 'products/gallery');
            $product->media()->create([
                'type' => MediaType::Image,
                'disk' => 'public',
                'path' => $path,
                'mime' => 'image/webp',
                'size' => null,
                'sort_order' => ++$sort,
            ]);
        }

        if ($request->filled('video_url')) {
            $product->media()->updateOrCreate(
                ['type' => MediaType::Video->value, 'url' => $request->string('video_url')],
                ['disk' => 'public', 'sort_order' => ++$sort],
            );
        }
    }

    private function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: 'product';
        $slug = $base;
        $i = 1;

        while (Product::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'currencies' => CurrencyEnum::options(),
            'statuses' => ProductStatus::options(),
        ];
    }
}
