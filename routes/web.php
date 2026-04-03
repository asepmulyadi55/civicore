<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\MyOverviewController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SessionConflictController;
use App\Http\Controllers\PrivateFileController;
use App\Http\Controllers\HomepageController;
use App\Http\Controllers\MediaController;

// ── Public homepage (React SPA) ───────────────────────────────────────────────
Route::get('/', fn() => view('spa'))->name('home');

// ── Internal API — Homepage content for React SPA ───────────────────────────
// Protected by X-Api-Key header (key injected into SPA via Blade meta tag).
Route::get('/api/homepage', [HomepageController::class, 'api'])
    ->middleware('api.key')
    ->name('api.homepage');

// ── Auth (public) ─────────────────────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ── Single-session conflict (public — no auth required) ────────────────────────
Route::get('/session-conflict', [SessionConflictController::class, 'show'])->name('session.conflict');
Route::post('/session-use-this', [SessionConflictController::class, 'useThisDevice'])->name('session.use-this');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:5,1');


// Google OAuth
Route::get('/auth/google/login', [SocialAuthController::class, 'redirectToGoogleLogin'])->name('auth.google.login');
Route::get('/auth/google/register', [SocialAuthController::class, 'redirectToGoogleRegister'])->name('auth.google.register');
Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);

// Forgot / Reset Password
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email')->middleware('throttle:5,1');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update')->middleware('throttle:5,1');

// ── Auth-protected pages ──────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Homepage CMS ─────────────────────────────────────────────────────────
    Route::get('/homepage', [HomepageController::class, 'index'])
        ->middleware('permission:homepage.view')->name('homepage.index');
    Route::post('/homepage/hero', [HomepageController::class, 'updateHero'])
        ->middleware('permission:homepage.edit')->name('homepage.hero');
    Route::post('/homepage/featured-event', [HomepageController::class, 'updateFeaturedEvent'])
        ->middleware('permission:homepage.edit')->name('homepage.featured-event');
    Route::post('/homepage/events', [HomepageController::class, 'storeEvent'])
        ->middleware('permission:homepage.create')->name('homepage.events.store');
    Route::put('/homepage/events/{id}', [HomepageController::class, 'updateEvent'])
        ->middleware('permission:homepage.edit')->name('homepage.events.update');
    Route::delete('/homepage/events/{id}', [HomepageController::class, 'destroyEvent'])
        ->middleware('permission:homepage.delete')->name('homepage.events.destroy');
    Route::post('/homepage/about', [HomepageController::class, 'updateAbout'])
        ->middleware('permission:homepage.edit')->name('homepage.about');

    // ── Private file serving (auth-protected) ─────────────────────────────────
    Route::get('/private/{path}', [PrivateFileController::class, 'serve'])
        ->where('path', '.+')
        ->name('private.file');

    // Resident personal overview (residents only, no secondary permission needed)
    Route::get('/my-overview', [MyOverviewController::class, 'index'])->name('my-overview');

    // ── Residents ─────────────────────────────────────────────────────────────
    Route::get('/residents', [ResidentController::class, 'index'])
        ->middleware('permission:residents.view')->name('residents.index');
    Route::post('/residents', [ResidentController::class, 'store'])
        ->middleware('permission:residents.create')->name('residents.store');
    Route::match(['PUT', 'PATCH'], '/residents/{resident}', [ResidentController::class, 'update'])
        ->middleware('permission:residents.edit')->name('residents.update');
    Route::patch('/residents/{resident}/deactivate', [ResidentController::class, 'deactivate'])
        ->middleware('permission:residents.edit')->name('residents.deactivate');
    Route::delete('/residents/{resident}', [ResidentController::class, 'destroy'])
        ->middleware('permission:residents.delete')->name('residents.destroy');

    // ── Blocks ────────────────────────────────────────────────────────────────
    Route::get('/blocks', [BlockController::class, 'index'])
        ->middleware('permission:blocks.view')->name('blocks.index');
    Route::post('/blocks', [BlockController::class, 'store'])
        ->middleware('permission:blocks.create')->name('blocks.store');
    Route::match(['PUT', 'PATCH'], '/blocks/{block}', [BlockController::class, 'update'])
        ->middleware('permission:blocks.edit')->name('blocks.update');
    Route::delete('/blocks/{block}', [BlockController::class, 'destroy'])
        ->middleware('permission:blocks.delete')->name('blocks.destroy');

    // ── Payments ──────────────────────────────────────────────────────────────
    Route::get('/payments', [PaymentController::class, 'index'])
        ->middleware('permission:payments.view')->name('payments.index');
    Route::post('/payments', [PaymentController::class, 'store'])
        ->middleware('permission:payments.create')->name('payments.store');
    Route::match(['PUT', 'PATCH'], '/payments/{payment}', [PaymentController::class, 'update'])
        ->middleware('permission:payments.edit')->name('payments.update');
    Route::patch('/payments/{payment}/approve', [PaymentController::class, 'approve'])
        ->middleware('permission:payments.approve')->name('payments.approve');
    Route::patch('/payments/{payment}/reject', [PaymentController::class, 'reject'])
        ->middleware('permission:payments.approve')->name('payments.reject');
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])
        ->middleware('permission:payments.delete')->name('payments.destroy');
    Route::post('/payments/batch/{batchId}/approve', [PaymentController::class, 'approveBatch'])
        ->middleware('permission:payments.approve')->name('payments.batch.approve');
    Route::post('/payments/batch/{batchId}/reject', [PaymentController::class, 'rejectBatch'])
        ->middleware('permission:payments.approve')->name('payments.batch.reject');

    // ── Reports ───────────────────────────────────────────────────────────────
    Route::get('/reports', [ReportController::class, 'index'])
        ->middleware('permission:reports.view')->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])
        ->middleware('permission:reports.view')->name('reports.export');

    // ── User Management ───────────────────────────────────────────────────────
    Route::get('/users', [UserController::class, 'index'])
        ->middleware('permission:users.view')->name('users.index');
    Route::post('/users', [UserController::class, 'store'])
        ->middleware('permission:users.create')->name('users.store');
    Route::match(['PUT', 'PATCH'], '/users/{user}', [UserController::class, 'update'])
        ->middleware('permission:users.edit')->name('users.update');
    Route::post('/users/{user}/approve', [UserController::class, 'approve'])
        ->middleware('permission:users.approve')->name('users.approve');
    Route::post('/users/check-resident-email', [UserController::class, 'checkResidentEmail'])
        ->middleware('throttle:30,1')->name('users.check-resident-email');
    Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate'])
        ->middleware('permission:users.edit')->name('users.deactivate');
    Route::patch('/users/{user}/reactivate', [UserController::class, 'reactivate'])
        ->middleware('permission:users.edit')->name('users.reactivate');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:users.delete')->name('users.destroy');


    // ── Roles ─────────────────────────────────────────────────────────────────
    Route::get('/roles', [RoleController::class, 'index'])
        ->middleware('permission:roles.view')->name('roles.index');
    Route::post('/roles', [RoleController::class, 'store'])
        ->middleware('permission:roles.create')->name('roles.store');
    Route::match(['PUT', 'PATCH'], '/roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:roles.edit')->name('roles.update');
    Route::patch('/roles/{role}/permissions', [RoleController::class, 'updatePermissions'])
        ->middleware('permission:roles.edit')->name('roles.permissions');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
        ->middleware('permission:roles.delete')->name('roles.destroy');

    // ── Media Manager ─────────────────────────────────────────────────────────
    Route::get('/media', [MediaController::class, 'index'])
        ->middleware('permission:media.view')->name('media.index');
    Route::delete('/media/{mediaFile}', [MediaController::class, 'destroy'])
        ->middleware('permission:media.delete')->name('media.destroy');
    Route::delete('/media', [MediaController::class, 'bulkDestroy'])
        ->middleware('permission:media.delete')->name('media.bulk-destroy');

    // ── Settings (profile — all roles) ────────────────────────────────────────
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings/profile', [SettingController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/settings/password', [SettingController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/reset-link', [SettingController::class, 'sendResetLink'])->name('settings.reset-link');
    Route::post('/settings/security', [SettingController::class, 'updateSecurity'])->middleware('permission:settings.edit')->name('settings.security');
});
