@php($tm = app(\App\Services\Tracking\TrackingManager::class))
@if ($tm->hasBrowserTracking())
    <script>{!! $tm->baseScripts() !!}</script>
    <script>
        window.fsTrack = function (internal, data, eventId) {
            data = data || {};
            var maps = {!! json_encode($tm->eventMaps(), JSON_UNESCAPED_SLASHES) !!};
            try { if (window.fbq && maps.meta && maps.meta[internal]) fbq('track', maps.meta[internal], data, eventId ? { eventID: eventId } : {}); } catch (e) {}
            try { if (window.ttq && maps.tiktok && maps.tiktok[internal]) ttq.track(maps.tiktok[internal], data, eventId ? { event_id: eventId } : undefined); } catch (e) {}
        };
    </script>
@else
    <script>window.fsTrack = function () {};</script>
@endif
