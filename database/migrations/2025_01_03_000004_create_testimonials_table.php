<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_page_id')->constrained()->cascadeOnDelete();
            $table->string('customer_name');
            $table->string('customer_image')->nullable();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->text('text');
            $table->string('video_url')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['landing_page_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
