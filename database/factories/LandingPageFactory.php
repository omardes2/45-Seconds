<?php

namespace Database\Factories;

use App\Enums\PageStatus;
use App\Models\LandingPage;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LandingPage>
 */
class LandingPageFactory extends Factory
{
    protected $model = LandingPage::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);

        return [
            'product_id' => Product::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(1, 99999),
            'status' => PageStatus::Draft,
            'title' => $this->faker->sentence(3),
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => PageStatus::Published,
            'published_at' => now(),
        ]);
    }
}
