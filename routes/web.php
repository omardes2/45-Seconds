<?php

use App\Enums\Permission;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TrackingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
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

    // Audit log
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
});
