<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_id')->unique();
            $table->uuid('visitor_id')->index();
            $table->foreignId('landing_page_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('referrer', 1024)->nullable();

            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('fbclid')->nullable();
            $table->string('ttclid')->nullable();

            $table->timestamps();

            $table->index(['landing_page_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
