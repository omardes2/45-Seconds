<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_page_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('price', 10, 2);
            $table->decimal('compare_at_price', 10, 2)->nullable();
            $table->string('badge_text')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['landing_page_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
