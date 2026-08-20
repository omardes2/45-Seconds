<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\LandingPage;
use App\Models\Offer;
use App\Services\Orders\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicOrderController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    public function store(StoreOrderRequest $request, LandingPage $page): RedirectResponse
    {
        // Orders are only accepted on a live, published page.
        abort_unless($page->isPublished() && $page->hasSnapshot(), 404);

        $offer = Offer::where('id', $request->integer('offer_id'))
            ->where('landing_page_id', $page->id)
            ->where('is_active', true)
            ->firstOrFail();

        $order = $this->orders->create(
            page: $page,
            offer: $offer,
            customer: $request->only(['full_name', 'phone', 'city', 'area', 'address', 'notes']),
            options: $this->cleanOptions($page, $offer, (array) $request->input('options', [])),
            meta: [
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'ip_address' => $request->ip(),
                'visitor_id' => $request->attributes->get('fs_visitor_id'),
                'session_id' => $request->attributes->get('fs_session_id'),
            ],
            attribution: (array) $request->attributes->get('fs_attribution', []),
        );

        return redirect()
            ->route('public.thankyou', ['page' => $page->slug])
            ->with('order_id', $order->id);
    }

    /**
     * Keep only defined variant groups/choices, one clean block per unit.
     *
     * @param  array<int, mixed>  $submitted
     * @return array<int, array<string, string>>
     */
    private function cleanOptions(LandingPage $page, Offer $offer, array $submitted): array
    {
        $groups = collect($page->published_snapshot['options'] ?? [])
            ->filter(fn ($g) => ! empty($g['name']) && ! empty($g['choices']));
        if ($groups->isEmpty()) {
            return [];
        }

        $units = max(1, (int) $offer->quantity);
        $clean = [];

        for ($i = 0; $i < $units; $i++) {
            $unit = [];
            foreach ($groups as $group) {
                $name = $group['name'];
                $choice = $submitted[$i][$name] ?? null;
                if (in_array($choice, $group['choices'], true)) {
                    $unit[$name] = $choice;
                }
            }
            $clean[] = $unit;
        }

        return $clean;
    }

    public function thankyou(Request $request, LandingPage $page): View|RedirectResponse
    {
        $orderId = $request->session()->get('order_id');
        $order = $orderId
            ? $page->orders()->with('offer')->find($orderId)
            : null;

        if (! $order) {
            return redirect()->route('public.show', $page->slug);
        }

        // Keep it available for a page refresh / pixel fire.
        $request->session()->keep('order_id');

        return view('public.thankyou', [
            'page' => $page,
            'order' => $order,
            'data' => $page->published_snapshot,
        ]);
    }
}
