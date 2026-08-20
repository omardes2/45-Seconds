<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OfferRequest;
use App\Models\LandingPage;
use App\Models\Offer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OfferController extends Controller
{
    public function index(LandingPage $page): View
    {
        $offers = $page->offers()->orderBy('sort_order')->get();

        return view('admin.offers.index', compact('page', 'offers'));
    }

    public function store(OfferRequest $request, LandingPage $page): RedirectResponse
    {
        DB::transaction(function () use ($request, $page) {
            $offer = $page->offers()->create($this->payload($request, $page));
            $this->normaliseDefault($page, $offer);
        });

        return redirect()->route('admin.pages.offers.index', $page)->with('success', 'تم إضافة العرض.');
    }

    public function edit(LandingPage $page, Offer $offer): View
    {
        abort_unless($offer->landing_page_id === $page->id, 404);

        return view('admin.offers.edit', compact('page', 'offer'));
    }

    public function update(OfferRequest $request, LandingPage $page, Offer $offer): RedirectResponse
    {
        abort_unless($offer->landing_page_id === $page->id, 404);

        DB::transaction(function () use ($request, $page, $offer) {
            $offer->update($this->payload($request, $page));
            $this->normaliseDefault($page, $offer);
        });

        return redirect()->route('admin.pages.offers.index', $page)->with('success', 'تم تحديث العرض.');
    }

    public function destroy(LandingPage $page, Offer $offer): RedirectResponse
    {
        abort_unless($offer->landing_page_id === $page->id, 404);

        $wasDefault = $offer->is_default;
        $offer->delete();

        // Promote another active offer to default if we removed the default one.
        if ($wasDefault) {
            $next = $page->offers()->where('is_active', true)->orderBy('sort_order')->first();
            $next?->update(['is_default' => true]);
        }

        return back()->with('success', 'تم حذف العرض.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(OfferRequest $request, LandingPage $page): array
    {
        $data = $request->validated();

        return [
            'name' => $data['name'],
            'quantity' => $data['quantity'],
            'price' => $data['price'],
            'compare_at_price' => $data['compare_at_price'] ?? null,
            'badge_text' => $data['badge_text'] ?? null,
            'is_default' => (bool) ($data['is_default'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? true),
            'sort_order' => $data['sort_order'] ?? ($page->offers()->max('sort_order') + 1),
        ];
    }

    /**
     * Enforce exactly one default offer on the page.
     */
    private function normaliseDefault(LandingPage $page, Offer $current): void
    {
        if ($current->is_default) {
            // This one is default → clear the flag on every other offer.
            $page->offers()->whereKeyNot($current->id)->update(['is_default' => false]);

            return;
        }

        // None marked default → make sure at least one remains default.
        if (! $page->offers()->where('is_default', true)->exists()) {
            $page->offers()->where('is_active', true)->orderBy('sort_order')->first()?->update(['is_default' => true]);
        }
    }
}
