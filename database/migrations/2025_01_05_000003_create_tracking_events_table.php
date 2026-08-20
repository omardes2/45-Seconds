<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_page_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('visitor_id')->nullable()->index();
            $table->uuid('session_id')->nullable();
            $table->string('type')->index();
            // Shared event id used for Browser <-> CAPI deduplication (Sprint 7).
            $table->uuid('event_id')->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['landing_page_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_events');
    }
};
