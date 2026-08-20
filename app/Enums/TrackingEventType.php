<?php

namespace App\Enums;

/**
 * Internal (first-party) conversion funnel events. These are distinct from the
 * vendor pixel event names (see App\Services\Tracking) — a mapping layer
 * translates these into Meta / TikTok event names.
 */
enum TrackingEventType: string
{
    case PageView = 'page_view';
    case ViewContent = 'view_content';
    case DemoInteraction = 'demo_interaction';
    case OfferSelected = 'offer_selected';
    case CheckoutOpened = 'checkout_opened';
    case OrderCreated = 'order_created';

    public function label(): string
    {
        return match ($this) {
            self::PageView => 'مشاهدة الصفحة',
            self::ViewContent => 'مشاهدة المحتوى',
            self::DemoInteraction => 'تفاعل مع العرض',
            self::OfferSelected => 'اختيار عرض',
            self::CheckoutOpened => 'فتح الطلب',
            self::OrderCreated => 'إنشاء طلب',
        };
    }
}
