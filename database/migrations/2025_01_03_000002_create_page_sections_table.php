<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_page_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['landing_page_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_sections');
    }
};
