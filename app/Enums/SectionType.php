<?php

namespace App\Enums;

/**
 * The structured "45 Seconds" section types. The public journey renders enabled
 * sections in this canonical order. New types can be appended without touching
 * the storage layer (page_sections.settings is a free-form JSON bag).
 */
enum SectionType: string
{
    case Hero = 'hero';
    case Problem = 'problem';
    case Demo = 'demo';
    case Benefits = 'benefits';
    case Testimonials = 'testimonials';
    case Offers = 'offers';
    case Trust = 'trust';
    case FinalCta = 'final_cta';

    public function label(): string
    {
        return match ($this) {
            self::Hero => 'البداية (Hook)',
            self::Problem => 'المشكلة',
            self::Demo => 'العرض التفاعلي',
            self::Benefits => 'المميزات',
            self::Testimonials => 'آراء العملاء',
            self::Offers => 'العروض',
            self::Trust => 'الثقة والأسئلة',
            self::FinalCta => 'الطلب النهائي',
        };
    }

    /**
     * The "seconds" marker shown on the timeline for this section.
     */
    public function second(): int
    {
        return match ($this) {
            self::Hero => 0,
            self::Problem => 5,
            self::Demo => 12,
            self::Benefits => 20,
            self::Testimonials => 27,
            self::Offers => 33,
            self::Trust => 38,
            self::FinalCta => 45,
        };
    }

    /**
     * Default (empty) settings scaffold for a freshly created section.
     *
     * @return array<string, mixed>
     */
    public function defaultSettings(): array
    {
        return match ($this) {
            self::Hero => [
                'badge' => null,
                'headline' => null,
                'highlight' => null,
                'subtitle' => null,
                'main_image' => null,
                'background_image' => null,
                'video_url' => null,
                'cta_text' => 'اطلب الآن',
                'delivery_text' => 'الدفع عند الاستلام',
                'show_price' => true,
            ],
            self::Problem => [
                'title' => null,
                'subtitle' => null,
                'image' => null,
                'items' => [], // [{icon,title,description}]
            ],
            self::Demo => [
                'title' => null,
                'subtitle' => null,
                'demo_type' => DemoType::Image->value,
                'image' => null,
                'video_url' => null,
                'before_image' => null,
                'after_image' => null,
            ],
            self::Benefits => [
                'title' => null,
                'subtitle' => null,
                'items' => [], // [{icon,title,description}]
            ],
            self::Testimonials => [
                'title' => 'ماذا قال عملاؤنا',
                'subtitle' => null,
            ],
            self::Offers => [
                'title' => 'اختر العرض المناسب',
                'subtitle' => null,
            ],
            self::Trust => [
                'title' => 'لماذا تثق بنا',
                'subtitle' => null,
                'items' => [], // [{icon,title,description}]
                'faq_title' => 'الأسئلة الشائعة',
            ],
            self::FinalCta => [
                'headline' => 'انتهت الـ45 ثانية. هل تريده؟',
                'subtitle' => null,
                'cta_text' => 'اطلب الآن',
            ],
        };
    }

    /**
     * Canonical journey order used to (re)build a page skeleton.
     *
     * @return array<int, self>
     */
    public static function journey(): array
    {
        return [
            self::Hero,
            self::Problem,
            self::Demo,
            self::Benefits,
            self::Testimonials,
            self::Offers,
            self::Trust,
            self::FinalCta,
        ];
    }
}
