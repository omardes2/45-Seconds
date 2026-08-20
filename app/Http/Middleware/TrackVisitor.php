<?php

namespace App\Http\Middleware;

use App\Enums\TrackingEventType;
use App\Models\LandingPage;
use App\Services\Tracking\VisitorTracker;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    public function __construct(private readonly VisitorTracker $tracker) {}

    public function handle(Request $request, Closure $next): Response
    {
        $page = $this->resolvePage($request);

        [$visitorId, $visit] = $this->tracker->resolve($request, $page);

        // Expose to downstream (order creation attribution snapshot).
        $request->attributes->set('fs_visitor_id', $visitorId);
        $request->attributes->set('fs_session_id', $visit->session_id);
        $request->attributes->set('fs_attribution', $visit->attributionData());

        // Record a page view for GET landing views.
        if ($request->routeIs('public.show') && $request->isMethod('GET')) {
            $this->tracker->recordEvent(TrackingEventType::PageView, $page, $visitorId, $visit->session_id);
        }

        $response = $next($request);

        Cookie::queue(cookie(
            name: config('fortyfive.visitor_cookie'),
            value: $visitorId,
            minutes: config('fortyfive.visitor_cookie_days') * 24 * 60,
            httpOnly: true,
            sameSite: 'lax',
        ));

        return $response;
    }

    private function resolvePage(Request $request): ?LandingPage
    {
        $page = $request->route('page');
        if ($page instanceof LandingPage) {
            return $page;
        }

        $slug = is_string($page) ? $page : $request->route('slug');

        return $slug ? LandingPage::where('slug', $slug)->first() : null;
    }
}
