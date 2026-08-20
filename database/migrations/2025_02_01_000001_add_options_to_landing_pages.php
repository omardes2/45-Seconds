<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The column may already exist from an earlier deploy; guard so the
        // migration is safe to run on any database state.
        if (Schema::hasColumn('landing_pages', 'options')) {
            return;
        }

        Schema::table('landing_pages', function (Blueprint $table) {
            // Customer-selectable variant groups, e.g. colours / sizes:
            // [{ "name": "צבע", "choices": ["אדום","כחול"] }]
            $table->json('options')->nullable()->after('settings');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('landing_pages', 'options')) {
            return;
        }

        Schema::table('landing_pages', function (Blueprint $table) {
            $table->dropColumn('options');
        });
    }
};
