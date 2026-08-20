<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\DemoType;
use App\Enums\SectionType;
use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use App\Models\PageSection;
use App\Services\AuditLogger;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageSectionController extends Controller
{
    /** Image setting keys per section type (uploaded via the editor). */
    private const IMAGE_KEYS = [
        'hero' => ['main_image', 'background_image'],
        'problem' => ['image'],
        'demo' => ['image', 'before_image', 'after_image'],
    ];

    public function __construct(
        private readonly MediaService $media,
        private readonly AuditLogger $audit,
    ) {}

    public function edit(LandingPage $page, PageSection $section): View
    {
        abort_unless($section->landing_page_id === $page->id, 404);

        return view('admin.pages.sections.edit', compact('page', 'section'));
    }

    public function update(Request $request, LandingPage $page, PageSection $section): RedirectResponse
    {
        abort_unless($section->landing_page_id === $page->id, 404);

        $type = $section->type instanceof SectionType ? $section->type : SectionType::from($section->type);

        $validated = $request->validate($this->rulesFor($type));

        $settings = array_merge($section->settings ?? [], $this->scalarSettings($type, $validated));

        // Repeatable item lists (problems / benefits / trust items).
        if (in_array($type, [SectionType::Problem, SectionType::Benefits, SectionType::Trust], true)) {
            $settings['items'] = $this->cleanItems($request->input('items', []));
        }

        // Handle image uploads / removals.
        foreach (self::IMAGE_KEYS[$type->value] ?? [] as $key) {
            if ($request->hasFile($key)) {
                $this->media->delete($section->settings[$key] ?? null);
                $settings[$key] = $this->media->storeImage($request->file($key), 'pages/'.$page->id);
            } elseif ($request->boolean('remove_'.$key)) {
                $this->media->delete($section->settings[$key] ?? null);
                $settings[$key] = null;
            }
        }

        $section->settings = $settings;
        $section->is_enabled = $request->boolean('is_enabled');
        $section->save();

        $page->forceFill(['updated_by' => $request->user()->id])->save();
        $this->audit->log(AuditAction::Updated, $page, ['section' => $type->value]);

        return redirect()->route('admin.pages.builder', $page)
            ->with('success', 'تم حفظ قسم «'.$type->label().'».');
    }

    public function toggle(Request $request, LandingPage $page, PageSection $section): RedirectResponse
    {
        abort_unless($section->landing_page_id === $page->id, 404);

        $section->update(['is_enabled' => ! $section->is_enabled]);

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function rulesFor(SectionType $type): array
    {
        $img = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'];

        return match ($type) {
            SectionType::Hero => [
                'badge' => ['nullable', 'string', 'max:80'],
                'headline' => ['nullable', 'string', 'max:160'],
                'highlight' => ['nullable', 'string', 'max:80'],
                'subtitle' => ['nullable', 'string', 'max:255'],
                'cta_text' => ['nullable', 'string', 'max:60'],
                'delivery_text' => ['nullable', 'string', 'max:120'],
                'video_url' => ['nullable', 'url', 'max:2048'],
                'show_price' => ['nullable', 'boolean'],
                'main_image' => $img,
                'background_image' => $img,
                'is_enabled' => ['nullable', 'boolean'],
            ],
            SectionType::Problem => [
                'title' => ['nullable', 'string', 'max:160'],
                'subtitle' => ['nullable', 'string', 'max:255'],
                'image' => $img,
                'items' => ['nullable', 'array', 'max:5'],
                'items.*.icon' => ['nullable', 'string', 'max:16'],
                'items.*.title' => ['nullable', 'string', 'max:120'],
                'items.*.description' => ['nullable', 'string', 'max:255'],
                'is_enabled' => ['nullable', 'boolean'],
            ],
            SectionType::Demo => [
                'title' => ['nullable', 'string', 'max:160'],
                'subtitle' => ['nullable', 'string', 'max:255'],
                'demo_type' => ['required', Rule::in(array_column(DemoType::cases(), 'value'))],
                'video_url' => ['nullable', 'url', 'max:2048'],
                'image' => $img,
                'before_image' => $img,
                'after_image' => $img,
                'is_enabled' => ['nullable', 'boolean'],
            ],
            SectionType::Benefits => [
                'title' => ['nullable', 'string', 'max:160'],
                'subtitle' => ['nullable', 'string', 'max:255'],
                'items' => ['nullable', 'array', 'max:12'],
                'items.*.icon' => ['nullable', 'string', 'max:16'],
                'items.*.title' => ['nullable', 'string', 'max:120'],
                'items.*.description' => ['nullable', 'string', 'max:255'],
                'is_enabled' => ['nullable', 'boolean'],
            ],
            SectionType::Trust => [
                'title' => ['nullable', 'string', 'max:160'],
                'subtitle' => ['nullable', 'string', 'max:255'],
                'faq_title' => ['nullable', 'string', 'max:160'],
                'items' => ['nullable', 'array', 'max:8'],
                'items.*.icon' => ['nullable', 'string', 'max:16'],
                'items.*.title' => ['nullable', 'string', 'max:120'],
                'items.*.description' => ['nullable', 'string', 'max:255'],
                'is_enabled' => ['nullable', 'boolean'],
            ],
            SectionType::FinalCta => [
                'headline' => ['nullable', 'string', 'max:160'],
                'subtitle' => ['nullable', 'string', 'max:255'],
                'cta_text' => ['nullable', 'string', 'max:60'],
                'is_enabled' => ['nullable', 'boolean'],
            ],
            default => ['is_enabled' => ['nullable', 'boolean']],
        };
    }

    /**
     * Pull only scalar (non-file, non-items) validated keys into settings.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function scalarSettings(SectionType $type, array $validated): array
    {
        $exclude = array_merge(['is_enabled', 'items'], self::IMAGE_KEYS[$type->value] ?? []);
        $out = [];

        foreach ($validated as $key => $value) {
            if (in_array($key, $exclude, true)) {
                continue;
            }
            if ($key === 'show_price') {
                $out[$key] = (bool) $value;

                continue;
            }
            $out[$key] = $value;
        }

        return $out;
    }

    /**
     * Drop fully-empty item rows and re-key sequentially.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, string>>
     */
    private function cleanItems(array $items): array
    {
        return collect($items)
            ->map(fn ($i) => [
                'icon' => trim((string) ($i['icon'] ?? '')),
                'title' => trim((string) ($i['title'] ?? '')),
                'description' => trim((string) ($i['description'] ?? '')),
            ])
            ->filter(fn ($i) => $i['title'] !== '' || $i['description'] !== '')
            ->values()
            ->all();
    }
}
