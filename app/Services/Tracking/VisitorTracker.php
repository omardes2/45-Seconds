<?php

namespace App\Services\Tracking;

use App\Enums\TrackingEventType;
use App\Models\LandingPage;
use App\Models\TrackingEvent;
use App\Models\Visit;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * First-party visitor + session tracking. Deliberately minimal: it stores only
 * what is needed for attribution and funnel analytics, keyed by a first-party
 * cookie (no third-party identifiers).
 */
class VisitorTracker
{
    /**
     * Resolve (and persist) the visitor + current visit for this request.
     * Returns [visitorId, Visit].
     *
     * @return array{0: string, 1: Visit}
     */
    public function resolve(Request $request, ?LandingPage $page): array
    {
        $visitorId = $request->cookie(config('fortyfive.visitor_cookie')) ?: (string) Str::uuid();

        Visitor::updateOrCreate(
            ['visitor_id' => $visitorId],
            [
                'last_seen_at' => now(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'ip_address' => $request->ip(),
            ] + ['first_seen_at' => now()],
        );

        $sessionId = $request->session()->get('fs_visit_id');
        $visit = $sessionId ? Visit::where('session_id', $sessionId)->first() : null;

        if (! $visit) {
            $sessionId = (string) Str::uuid();
            $request->session()->put('fs_visit_id', $sessionId);

            $visit = Visit::create(array_merge(
                [
                    'session_id' => $sessionId,
                    'visitor_id' => $visitorId,
                    'landing_page_id' => $page?->id,
                    'first_seen_at' => now(),
                    'last_seen_at' => now(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 255),
                    'ip_address' => $request->ip(),
                    'referrer' => substr((string) $request->headers->get('referer'), 0, 1024) ?: null,
                ],
                $this->utmFrom($request),
            ));
        } else {
            $visit->update([
                'last_seen_at' => now(),
                'landing_page_id' => $page?->id ?? $visit->landing_page_id,
            ]);
        }

        return [$visitorId, $visit];
    }

    /**
     * Record a funnel event. Deduplicated by event_id when supplied.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function recordEvent(
        TrackingEventType $type,
        ?LandingPage $page,
        ?string $visitorId,
        ?string $sessionId,
        array $metadata = [],
        ?string $eventId = null,
    ): TrackingEvent {
        $attributes = [
            'landing_page_id' => $page?->id,
            'visitor_id' => $visitorId,
            'session_id' => $sessionId,
            'type' => $type,
            'metadata' => $metadata ?: null,
            'created_at' => now(),
        ];

        if ($eventId) {
            return TrackingEvent::firstOrCreate(['event_id' => $eventId], $attributes);
        }

        return TrackingEvent::create(array_merge($attributes, ['event_id' => (string) Str::uuid()]));
    }

    /**
     * @return array<string, string|null>
     */
    private function utmFrom(Request $request): array
    {
        $keys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid', 'ttclid'];
        $out = [];
        foreach ($keys as $key) {
            $value = $request->query($key);
            $out[$key] = is_string($value) ? substr($value, 0, 255) : null;
        }

        return $out;
    }
}
