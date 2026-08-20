<?php

namespace App\Enums;

enum AuditAction: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case Archived = 'archived';
    case Published = 'published';
    case Paused = 'paused';
    case OrderStatusChanged = 'order_status_changed';
    case TrackingUpdated = 'tracking_updated';
    case SettingsUpdated = 'settings_updated';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'إنشاء',
            self::Updated => 'تعديل',
            self::Deleted => 'حذف',
            self::Archived => 'أرشفة',
            self::Published => 'نشر',
            self::Paused => 'إيقاف',
            self::OrderStatusChanged => 'تغيير حالة الطلب',
            self::TrackingUpdated => 'تعديل التتبع',
            self::SettingsUpdated => 'تعديل الإعدادات',
        };
    }
}
