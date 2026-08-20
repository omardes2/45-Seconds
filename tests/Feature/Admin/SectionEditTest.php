<?php

namespace Tests\Feature\Admin;

use App\Enums\SectionType;
use App\Models\LandingPage;
use App\Services\Pages\PageBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SectionEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_testimonials_and_offers_section_titles_are_saved(): void
    {
        $staff = $this->staff();
        $page = LandingPage::factory()->create();
        app(PageBuilder::class)->ensureSkeleton($page);

        foreach ([SectionType::Testimonials, SectionType::Offers] as $type) {
            $section = $page->sections()->where('type', $type->value)->firstOrFail();

            $this->actingAs($staff)->put(route('admin.pages.sections.update', [$page, $section]), [
                'is_enabled' => '1',
                'title' => 'عنوان جديد '.$type->value,
                'subtitle' => 'وصف',
            ])->assertRedirect();

            $this->assertSame('عنوان جديد '.$type->value, $section->fresh()->settings['title']);
        }
    }
}
