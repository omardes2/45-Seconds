<?php

namespace Tests\Feature\Admin;

use App\Models\LandingPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_page_can_be_deleted(): void
    {
        $staff = $this->staff();
        $page = LandingPage::factory()->create();

        $this->actingAs($staff)->delete(route('admin.pages.destroy', $page))
            ->assertRedirect(route('admin.pages.index'));

        $this->assertSoftDeleted($page);

        $this->actingAs($staff)->get(route('admin.pages.index'))
            ->assertDontSee($page->name);
    }
}
