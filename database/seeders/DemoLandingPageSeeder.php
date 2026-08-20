<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Builds the full "Sleep Light" demo experience (product, landing page, all
 * sections, offers, testimonials, FAQ). Fully implemented in the content
 * sprints; guarded so early-sprint seeding stays green before those tables
 * exist.
 */
class DemoLandingPageSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('landing_pages') || ! Schema::hasTable('products')) {
            return;
        }

        // Implemented in Sprint 9 (see the DemoContent builder).
    }
}
