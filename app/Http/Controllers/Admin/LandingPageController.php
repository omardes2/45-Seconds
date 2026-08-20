<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use App\Models\Product;
use App\Services\Analytics\AnalyticsService;
use App\Services\AuditLogger;
use App\Services\Pages\PageBuilder;
use App\Services\Pages\PagePublisher;
use App\Services\Pages\PageSnapshotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function __construct(
        private readonly PageBuilder $builder,
        private readonly PagePublisher $publisher,
        private readonly PageSnapshotService $snapshots,
        private readonly AuditLogger $audit,
    ) {}

    public function index(AnalyticsService $analytics): View
    {
        $pages = LandingPage::with('product')
            ->withCount('orders')
            ->latest()
            ->paginate(15);

        // Keyed all-time stats for the visible pages.
        $stats = collect($analytics->pageBreakdown())->keyBy('id');

        return view('admin.pages.index', compact('pages', 'stats'));
    }

    public function create(): View
    {
        $products = Product::where('status', ProductStatus::Active)->orderBy('name')->get();

        return view('admin.pages.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', Rule::exists('products', 'id')->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $page = $this->builder->createForProduct($product, $data['name'], $request->user()->id);

        $this->audit->log(AuditAction::Created, $page, ['name' => $page->name]);

        return redirect()->route('admin.pages.builder', $page)->with('success', 'تم إنشاء الصفحة. ابدأ بتعبئة الأقسام.');
    }

    /**
     * Builder hub — lists the journey steps for mobile step-by-step editing.
     */
    public function builder(LandingPage $page): View
    {
        $this->builder->ensureSkeleton($page);

        $page->load(['product', 'sections', 'offers', 'testimonials', 'faqs']);

        return view('admin.pages.builder', compact('page'));
    }

    public function editMeta(LandingPage $page): View
    {
        return view('admin.pages.meta', compact('page'));
    }

    public function updateMeta(Request $request, LandingPage $page): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('landing_pages', 'slug')->ignore($page->id)],
            'title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:500'],
        ]);

        $page->fill($data);
        $page->updated_by = $request->user()->id;
        $page->save();

        $this->audit->log(AuditAction::Updated, $page, ['name' => $page->name]);

        return back()->with('success', 'تم حفظ إعدادات الصفحة.');
    }

    public function preview(LandingPage $page): View
    {
        // Live preview from current DRAFT data (never the published snapshot).
        $snapshot = $this->snapshots->build($page);

        return view('public.landing', [
            'data' => $snapshot,
            'preview' => true,
            'page' => $page,
        ]);
    }

    public function publish(LandingPage $page): RedirectResponse
    {
        if ($page->offers()->where('is_active', true)->count() === 0) {
            return back()->with('error', 'أضف عرضًا واحدًا على الأقل قبل النشر.');
        }

        $this->publisher->publish($page);

        return back()->with('success', 'تم نشر الصفحة. الرابط: '.$page->publicUrl());
    }

    public function pause(LandingPage $page): RedirectResponse
    {
        $this->publisher->pause($page);

        return back()->with('success', 'تم إيقاف الصفحة.');
    }

    public function archive(LandingPage $page): RedirectResponse
    {
        $this->publisher->archive($page);

        return redirect()->route('admin.pages.index')->with('success', 'تم أرشفة الصفحة.');
    }
}
