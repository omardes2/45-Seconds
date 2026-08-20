<?php

use App\Enums\Permission;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\LandingPageController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageSectionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\TrackingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\PublicOrderController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\TrackingEventController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest / authentication routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/', fn () => redirect()->route('admin.dashboard'));

/*
|--------------------------------------------------------------------------
| Public landing pages (mobile-only 45 Seconds experience)
|--------------------------------------------------------------------------
*/
Route::middleware('track')->group(function () {
    Route::get('/p/{slug}', [PublicPageController::class, 'show'])->name('public.show');
    Route::post('/p/{page:slug}/order', [PublicOrderController::class, 'store'])
        ->middleware('throttle:checkout')
        ->name('public.order.store');
    Route::get('/p/{page:slug}/thank-you', [PublicOrderController::class, 'thankyou'])->name('public.thankyou');
});

// Client-side funnel events (view_content, demo_interaction, offer_selected, checkout_opened).
Route::post('/t/event', [TrackingEventController::class, 'store'])
    ->middleware(['track', 'throttle:60,1'])
    ->name('track.event');

/*
|--------------------------------------------------------------------------
| Admin panel
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    // Users (Super Admin / Manage Users)
    Route::middleware('can:'.Permission::ManageUsers->value)->group(function () {
        Route::resource('users', UserController::class)->except('show');
    });

    // Settings (Manage Settings — Super Admin only by default)
    Route::middleware('can:'.Permission::ManageSettings->value)->group(function () {
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });

    // Tracking integrations
    Route::middleware('can:'.Permission::ManageTracking->value)->group(function () {
        Route::get('tracking', [TrackingController::class, 'edit'])->name('tracking.edit');
        Route::put('tracking', [TrackingController::class, 'update'])->name('tracking.update');
    });

    // Products
    Route::middleware('can:'.Permission::ManageProducts->value)->group(function () {
        Route::resource('products', ProductController::class)->except('show');
        Route::delete('products/{product}/media/{media}', [ProductController::class, 'destroyMedia'])
            ->name('products.media.destroy');
    });

    // Landing pages & page builder
    Route::middleware('can:'.Permission::ManagePages->value)->group(function () {
        Route::get('pages', [LandingPageController::class, 'index'])->name('pages.index');
        Route::get('pages/create', [LandingPageController::class, 'create'])->name('pages.create');
        Route::post('pages', [LandingPageController::class, 'store'])->name('pages.store');
        Route::get('pages/{page}/builder', [LandingPageController::class, 'builder'])->name('pages.builder');
        Route::get('pages/{page}/meta', [LandingPageController::class, 'editMeta'])->name('pages.meta');
        Route::put('pages/{page}/meta', [LandingPageController::class, 'updateMeta'])->name('pages.meta.update');
        Route::get('pages/{page}/preview', [LandingPageController::class, 'preview'])->name('pages.preview');
        Route::post('pages/{page}/publish', [LandingPageController::class, 'publish'])->name('pages.publish');
        Route::post('pages/{page}/pause', [LandingPageController::class, 'pause'])->name('pages.pause');
        Route::delete('pages/{page}', [LandingPageController::class, 'archive'])->name('pages.archive');
        Route::delete('pages/{page}/delete', [LandingPageController::class, 'destroy'])->name('pages.destroy');

        // Section editors (hero / problem / demo / benefits / trust / final_cta)
        Route::get('pages/{page}/sections/{section}/edit', [PageSectionController::class, 'edit'])->name('pages.sections.edit');
        Route::put('pages/{page}/sections/{section}', [PageSectionController::class, 'update'])->name('pages.sections.update');
        Route::post('pages/{page}/sections/{section}/toggle', [PageSectionController::class, 'toggle'])->name('pages.sections.toggle');

        // Offers
        Route::get('pages/{page}/offers', [OfferController::class, 'index'])->name('pages.offers.index');
        Route::post('pages/{page}/offers', [OfferController::class, 'store'])->name('pages.offers.store');
        Route::get('pages/{page}/offers/{offer}/edit', [OfferController::class, 'edit'])->name('pages.offers.edit');
        Route::put('pages/{page}/offers/{offer}', [OfferController::class, 'update'])->name('pages.offers.update');
        Route::delete('pages/{page}/offers/{offer}', [OfferController::class, 'destroy'])->name('pages.offers.destroy');

        // Testimonials
        Route::get('pages/{page}/testimonials', [TestimonialController::class, 'index'])->name('pages.testimonials.index');
        Route::post('pages/{page}/testimonials', [TestimonialController::class, 'store'])->name('pages.testimonials.store');
        Route::get('pages/{page}/testimonials/{testimonial}/edit', [TestimonialController::class, 'edit'])->name('pages.testimonials.edit');
        Route::put('pages/{page}/testimonials/{testimonial}', [TestimonialController::class, 'update'])->name('pages.testimonials.update');
        Route::delete('pages/{page}/testimonials/{testimonial}', [TestimonialController::class, 'destroy'])->name('pages.testimonials.destroy');

        // FAQs
        Route::get('pages/{page}/faqs', [FaqController::class, 'index'])->name('pages.faqs.index');
        Route::post('pages/{page}/faqs', [FaqController::class, 'store'])->name('pages.faqs.store');
        Route::get('pages/{page}/faqs/{faq}/edit', [FaqController::class, 'edit'])->name('pages.faqs.edit');
        Route::put('pages/{page}/faqs/{faq}', [FaqController::class, 'update'])->name('pages.faqs.update');
        Route::delete('pages/{page}/faqs/{faq}', [FaqController::class, 'destroy'])->name('pages.faqs.destroy');
    });

    // Orders
    Route::middleware('can:'.Permission::ManageOrders->value)->group(function () {
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::put('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::post('orders/{order}/notes', [OrderController::class, 'addNote'])->name('orders.notes');
        Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
    });

    // Analytics
    Route::middleware('can:'.Permission::ViewAnalytics->value)->group(function () {
        Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    });

    // Audit log (sensitive — Super Admin / settings managers only)
    Route::middleware('can:'.Permission::ManageSettings->value)->group(function () {
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});
