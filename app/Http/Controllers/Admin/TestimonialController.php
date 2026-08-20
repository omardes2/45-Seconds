<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TestimonialRequest;
use App\Models\LandingPage;
use App\Models\Testimonial;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function __construct(private readonly MediaService $media) {}

    public function index(LandingPage $page): View
    {
        $testimonials = $page->testimonials()->orderBy('sort_order')->get();

        return view('admin.testimonials.index', compact('page', 'testimonials'));
    }

    public function store(TestimonialRequest $request, LandingPage $page): RedirectResponse
    {
        $data = $this->payload($request, $page);

        if ($request->hasFile('customer_image')) {
            $data['customer_image'] = $this->media->storeImage($request->file('customer_image'), 'testimonials');
        }

        $page->testimonials()->create($data);

        return redirect()->route('admin.pages.testimonials.index', $page)->with('success', 'تم إضافة الرأي.');
    }

    public function edit(LandingPage $page, Testimonial $testimonial): View
    {
        abort_unless($testimonial->landing_page_id === $page->id, 404);

        return view('admin.testimonials.edit', compact('page', 'testimonial'));
    }

    public function update(TestimonialRequest $request, LandingPage $page, Testimonial $testimonial): RedirectResponse
    {
        abort_unless($testimonial->landing_page_id === $page->id, 404);

        $data = $this->payload($request, $page);

        if ($request->hasFile('customer_image')) {
            $this->media->delete($testimonial->customer_image);
            $data['customer_image'] = $this->media->storeImage($request->file('customer_image'), 'testimonials');
        }

        $testimonial->update($data);

        return redirect()->route('admin.pages.testimonials.index', $page)->with('success', 'تم تحديث الرأي.');
    }

    public function destroy(LandingPage $page, Testimonial $testimonial): RedirectResponse
    {
        abort_unless($testimonial->landing_page_id === $page->id, 404);

        $this->media->delete($testimonial->customer_image);
        $testimonial->delete();

        return back()->with('success', 'تم حذف الرأي.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(TestimonialRequest $request, LandingPage $page): array
    {
        $data = $request->validated();

        return [
            'customer_name' => $data['customer_name'],
            'rating' => $data['rating'],
            'text' => $data['text'],
            'video_url' => $data['video_url'] ?? null,
            'is_verified' => (bool) ($data['is_verified'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? true),
            'sort_order' => $data['sort_order'] ?? ($page->testimonials()->max('sort_order') + 1),
        ];
    }
}
