<?php

namespace Database\Factories;

use App\Enums\CurrencyEnum;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\LandingPage;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $product = Product::factory();

        return [
            'order_number' => '45'.$this->faker->unique()->numberBetween(1000, 999999),
            'landing_page_id' => LandingPage::factory(),
            'product_id' => $product,
            'offer_id' => null,
            'full_name' => $this->faker->name(),
            'phone' => '059'.$this->faker->numerify('#######'),
            'city' => $this->faker->city(),
            'area' => $this->faker->streetName(),
            'address' => $this->faker->address(),
            'notes' => null,
            'quantity' => 1,
            'unit_price' => 89,
            'subtotal' => 89,
            'total' => 89,
            'currency' => CurrencyEnum::ILS,
            'payment_method' => PaymentMethod::CashOnDelivery,
            'status' => OrderStatus::New,
        ];
    }
}
