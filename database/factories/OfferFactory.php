<?php

namespace Database\Factories;

use App\Models\LandingPage;
use App\Models\Offer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Offer>
 */
class OfferFactory extends Factory
{
    protected $model = Offer::class;

    public function definition(): array
    {
        return [
            'landing_page_id' => LandingPage::factory(),
            'name' => 'قطعة واحدة',
            'quantity' => 1,
            'price' => $this->faker->randomFloat(2, 50, 300),
            'compare_at_price' => null,
            'badge_text' => null,
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
