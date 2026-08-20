<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_attributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('landing_page_id')->nullable()->constrained()->nullOnDelete();
            $table->string('visitor_id')->nullable();
            $table->string('session_id')->nullable();

            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();

            $table->string('fbclid')->nullable();
            $table->string('ttclid')->nullable();
            $table->string('referrer', 1024)->nullable();

            $table->timestamp('created_at')->nullable();

            $table->unique('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_attributions');
    }
};
