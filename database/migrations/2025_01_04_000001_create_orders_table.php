<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();

            $table->foreignId('landing_page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('offer_id')->nullable()->constrained()->nullOnDelete();

            // Customer
            $table->string('full_name');
            $table->string('phone', 40);
            $table->string('city');
            $table->string('area')->nullable();
            $table->string('address');
            $table->text('notes')->nullable();

            // Money — always recomputed server-side from the offer in the DB.
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('total', 10, 2);
            $table->string('currency', 3);
            $table->string('payment_method')->default('cod');

            $table->string('status')->default('new')->index();

            // Attribution (populated in Sprint 6)
            $table->string('visitor_id')->nullable()->index();
            $table->string('session_id')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('ip_address', 45)->nullable();

            $table->timestamps();

            $table->index('phone');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
