<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqRequest;
use App\Models\Faq;
use App\Models\LandingPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(LandingPage $page): View
    {
        $faqs = $page->faqs()->orderBy('sort_order')->get();

        return view('admin.faqs.index', compact('page', 'faqs'));
    }

    public function store(FaqRequest $request, LandingPage $page): RedirectResponse
    {
        $page->faqs()->create($this->payload($request, $page));

        return redirect()->route('admin.pages.faqs.index', $page)->with('success', 'تمت إضافة السؤال.');
    }

    public function edit(LandingPage $page, Faq $faq): View
    {
        abort_unless($faq->landing_page_id === $page->id, 404);

        return view('admin.faqs.edit', compact('page', 'faq'));
    }

    public function update(FaqRequest $request, LandingPage $page, Faq $faq): RedirectResponse
    {
        abort_unless($faq->landing_page_id === $page->id, 404);

        $faq->update($this->payload($request, $page));

        return redirect()->route('admin.pages.faqs.index', $page)->with('success', 'تم تحديث السؤال.');
    }

    public function destroy(LandingPage $page, Faq $faq): RedirectResponse
    {
        abort_unless($faq->landing_page_id === $page->id, 404);

        $faq->delete();

        return back()->with('success', 'تم حذف السؤال.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(FaqRequest $request, LandingPage $page): array
    {
        $data = $request->validated();

        return [
            'question' => $data['question'],
            'answer' => $data['answer'],
            'is_active' => (bool) ($data['is_active'] ?? true),
            'sort_order' => $data['sort_order'] ?? ($page->faqs()->max('sort_order') + 1),
        ];
    }
}
