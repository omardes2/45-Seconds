<?php

namespace Tests\Feature\Admin;

use App\Enums\CurrencyEnum;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_create_a_product_with_auto_slug(): void
    {
        $staff = $this->staff();

        $this->actingAs($staff)->post(route('admin.products.store'), [
            'name' => 'ضوء النوم',
            'base_price' => '89.00',
            'currency' => CurrencyEnum::ILS->value,
            'status' => ProductStatus::Active->value,
        ])->assertRedirect();

        $product = Product::first();
        $this->assertNotNull($product);
        $this->assertNotEmpty($product->slug);
        $this->assertSame('89.00', $product->base_price);
        $this->assertSame(CurrencyEnum::ILS, $product->currency);
    }

    public function test_slugs_are_unique(): void
    {
        $staff = $this->staff();
        Product::factory()->create(['slug' => 'sleep-light']);

        $this->actingAs($staff)->post(route('admin.products.store'), [
            'name' => 'Sleep Light',
            'slug' => 'sleep-light',
            'base_price' => '10',
            'currency' => 'ILS',
            'status' => 'active',
        ])->assertSessionHasErrors('slug');
    }

    public function test_compare_price_must_be_gte_base_price(): void
    {
        $staff = $this->staff();

        $this->actingAs($staff)->post(route('admin.products.store'), [
            'name' => 'Item',
            'base_price' => '100',
            'compare_at_price' => '50',
            'currency' => 'ILS',
            'status' => 'active',
        ])->assertSessionHasErrors('compare_at_price');
    }

    public function test_main_image_is_stored_and_converted(): void
    {
        Storage::fake('public');
        $staff = $this->staff();

        $this->actingAs($staff)->post(route('admin.products.store'), [
            'name' => 'With Image',
            'base_price' => '20',
            'currency' => 'ILS',
            'status' => 'active',
            'main_image' => UploadedFile::fake()->image('photo.jpg', 600, 600),
        ])->assertRedirect();

        $product = Product::first();
        $this->assertNotNull($product->main_image);
        Storage::disk('public')->assertExists($product->main_image);
    }

    public function test_upload_rejects_non_image_file(): void
    {
        Storage::fake('public');
        $staff = $this->staff();

        $this->actingAs($staff)->post(route('admin.products.store'), [
            'name' => 'Bad Upload',
            'base_price' => '20',
            'currency' => 'ILS',
            'status' => 'active',
            'main_image' => UploadedFile::fake()->create('malware.php', 40, 'application/x-php'),
        ])->assertSessionHasErrors('main_image');
    }

    public function test_users_without_permission_cannot_manage_products(): void
    {
        // A user with no roles/permissions.
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.products.index'))->assertForbidden();
    }
}
