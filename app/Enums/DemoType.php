<?php

namespace App\Enums;

enum DemoType: string
{
    case Image = 'image';
    case Video = 'video';
    case BeforeAfter = 'before_after';

    public function label(): string
    {
        return match ($this) {
            self::Image => 'صورة',
            self::Video => 'فيديو',
            self::BeforeAfter => 'قبل / بعد',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $c) => [$c->value => $c->label()])
            ->all();
    }
}
