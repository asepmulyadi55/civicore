<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\MyOverviewController;

// ── Auth ──────────────────────────────────────────────────────────────────
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Google OAuth
Route::get('/auth/google/login', [SocialAuthController::class, 'redirectToGoogleLogin'])->name('auth.google.login');
Route::get('/auth/google/register', [SocialAuthController::class, 'redirectToGoogleRegister'])->name('auth.google.register');
Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);

// Forgot / Reset Password
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

// ── Auth-protected pages ──────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Admin dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Resident personal overview
    Route::get('/my-overview', [MyOverviewController::class, 'index'])->name('my-overview');

    // Residents CRUD
    Route::resource('residents', ResidentController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    // Blocks CRUD
    Route::resource('blocks', BlockController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    // Payments (admin)
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::patch('/payments/{payment}/approve', [PaymentController::class, 'approve'])->name('payments.approve');
    Route::patch('/payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // User Management (stub for now)
    Route::get('/users', function () {
        return view('users');
    })->name('users.index');

    // Coming Soon pages
    Route::get('/events', function () {
        return view('coming-soon', [
            'feature' => 'Events',
            'icon' => 'event',
            'description' => 'Plan and manage community events, announcements, and schedules. This feature is currently under development.',
        ]);
    })->name('events.index');

    Route::get('/settings', function () {
        return view('coming-soon', [
            'feature' => 'Settings',
            'icon' => 'settings',
            'description' => 'Manage application-wide settings, currency, billing cycles, and more. This feature is currently under development.',
        ]);
    })->name('settings.index');
});
