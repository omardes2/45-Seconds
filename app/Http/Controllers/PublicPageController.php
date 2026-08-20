<?php

namespace App\Http\Controllers;

use App\Models\LandingPage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicPageController extends Controller
{
    /**
     * Render a published landing page from its stored snapshot (never from
     * live draft data). Only Published pages are publicly reachable.
     */
    public function show(Request $request, string $slug): View
    {
        $page = LandingPage::where('slug', $slug)->firstOrFail();

        abort_unless($page->isPublished() && $page->hasSnapshot(), 404);

        return view('public.landing', [
            'data' => $page->published_snapshot,
            'preview' => false,
            'page' => $page,
        ]);
    }
}
