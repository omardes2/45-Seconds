<?php

namespace App\Http\Controllers;

use App\Enums\TrackingEventType;
use App\Models\LandingPage;
use App\Services\Tracking\VisitorTracker;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TrackingEventController extends Controller
{
    /** Client events an untrusted browser is allowed to report. */
    private const ALLOWED = [
        'view_content',
        'demo_interaction',
        'offer_selected',
        'checkout_opened',
    ];

    public function __construct(private readonly VisitorTracker $tracker) {}

    public function store(Request $request, VisitorTracker $tracker): Response
    {
        $data = $request->validate([
            'type' => ['required', 'string', 'in:'.implode(',', self::ALLOWED)],
            'page_id' => ['nullable', 'integer'],
            'event_id' => ['nullable', 'string', 'max:64'],
            'metadata' => ['nullable', 'array'],
        ]);

        $page = ! empty($data['page_id']) ? LandingPage::find($data['page_id']) : null;

        $tracker->recordEvent(
            type: TrackingEventType::from($data['type']),
            page: $page,
            visitorId: $request->attributes->get('fs_visitor_id'),
            sessionId: $request->attributes->get('fs_session_id'),
            metadata: array_slice($data['metadata'] ?? [], 0, 10, true),
            eventId: $data['event_id'] ?? null,
        );

        return response()->noContent();
    }
}
