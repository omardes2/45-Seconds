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
        if (Schema::hasColumn('orders', 'options')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            // Per-unit variant selections, e.g. [{"צבע":"אדום"},{"צבע":"כחול"}].
            $table->json('options')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('orders', 'options')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('options');
        });
    }
};
