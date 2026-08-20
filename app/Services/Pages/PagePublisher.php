<?php

namespace App\Services\Pages;

use App\Enums\AuditAction;
use App\Enums\PageStatus;
use App\Models\LandingPage;
use App\Services\AuditLogger;

class PagePublisher
{
    public function __construct(
        private readonly PageSnapshotService $snapshots,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * Publish (or re-publish) a page: rebuild the snapshot from current draft
     * data and expose it to the public.
     */
    public function publish(LandingPage $page): LandingPage
    {
        $page->published_snapshot = $this->snapshots->build($page);
        $page->status = PageStatus::Published;
        $page->published_at = $page->published_at ?? now();
        $page->save();

        $this->audit->log(AuditAction::Published, $page, ['slug' => $page->slug]);

        return $page;
    }

    public function pause(LandingPage $page): LandingPage
    {
        $page->status = PageStatus::Paused;
        $page->save();

        $this->audit->log(AuditAction::Paused, $page, ['slug' => $page->slug]);

        return $page;
    }

    public function archive(LandingPage $page): LandingPage
    {
        $page->status = PageStatus::Archived;
        $page->save();

        $this->audit->log(AuditAction::Archived, $page, ['slug' => $page->slug]);

        return $page;
    }
}
