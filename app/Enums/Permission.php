<?php

namespace App\Enums;

/**
 * The full permission catalogue. Kept intentionally small (module-level, not
 * per-action) to avoid RBAC over-engineering. Super Admin bypasses all checks
 * via a Gate::before hook.
 */
enum Permission: string
{
    case ManagePages = 'manage_pages';
    case ManageProducts = 'manage_products';
    case ManageOrders = 'manage_orders';
    case ManageTestimonials = 'manage_testimonials';
    case ManageOffers = 'manage_offers';
    case ManageTracking = 'manage_tracking';
    case ViewAnalytics = 'view_analytics';
    case ManageUsers = 'manage_users';
    case ManageSettings = 'manage_settings';

    public function label(): string
    {
        return match ($this) {
            self::ManagePages => 'إدارة الصفحات',
            self::ManageProducts => 'إدارة المنتجات',
            self::ManageOrders => 'إدارة الطلبات',
            self::ManageTestimonials => 'إدارة آراء العملاء',
            self::ManageOffers => 'إدارة العروض',
            self::ManageTracking => 'إدارة التتبع',
            self::ViewAnalytics => 'عرض التحليلات',
            self::ManageUsers => 'إدارة المستخدمين',
            self::ManageSettings => 'إدارة الإعدادات',
        };
    }

    /**
     * Permissions granted to the "Admin / Staff" role by default.
     *
     * @return array<int, self>
     */
    public static function staffDefaults(): array
    {
        return [
            self::ManagePages,
            self::ManageProducts,
            self::ManageOrders,
            self::ManageTestimonials,
            self::ManageOffers,
            self::ManageTracking,
            self::ViewAnalytics,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
