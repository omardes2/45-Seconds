<?php

namespace Database\Factories;

use App\Enums\CurrencyEnum;
use App\Enums\ProductStatus;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(1, 99999),
            'sku' => strtoupper($this->faker->bothify('SKU-####')),
            'description' => $this->faker->sentence(),
            'base_price' => $this->faker->randomFloat(2, 20, 500),
            'compare_at_price' => null,
            'currency' => CurrencyEnum::ILS,
            'status' => ProductStatus::Active,
            'main_image' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => ProductStatus::Draft]);
    }
}
