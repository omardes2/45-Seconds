<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status')->default('draft')->index();
            $table->string('title')->nullable();

            // SEO / OpenGraph
            $table->string('meta_description', 500)->nullable();
            $table->string('og_title')->nullable();
            $table->string('og_description', 500)->nullable();
            $table->string('og_image')->nullable();

            $table->timestamp('published_at')->nullable();
            $table->json('settings')->nullable();

            // Published snapshot — public rendering reads from here, isolating
            // visitors from unpublished draft edits (see docs/DECISIONS.md).
            $table->longText('published_snapshot')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_pages');
    }
};
