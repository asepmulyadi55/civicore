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
use App\Http\Controllers\UnitController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SessionConflictController;
use App\Http\Controllers\PrivateFileController;
use App\Http\Controllers\HomepageController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\FamilyMemberController;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\SensitiveDataController;
use App\Http\Controllers\Api\BlockController as ApiBlockController;
use App\Http\Controllers\Api\ResidentController as ApiResidentController;
use App\Http\Controllers\Api\HomepageController as ApiHomepageController;

// ── Public homepage (React SPA) ───────────────────────────────────────────────
Route::get('/', fn() => view('spa'))->name('home');

// ── Internal API — Homepage content for React SPA ───────────────────────────
// Protected by X-Api-Key header (key injected into SPA via Blade meta tag).
Route::get('/api/homepage', [ApiHomepageController::class, 'index'])
    ->middleware('api.key')
    ->name('api.homepage');

// ── Auth (public) ─────────────────────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ── Single-session conflict (public — no auth required) ────────────────────────
Route::get('/session-conflict', [SessionConflictController::class, 'show'])->name('session.conflict');
Route::post('/session-use-this', [SessionConflictController::class, 'useThisDevice'])
    ->middleware('throttle:5,1')
    ->name('session.use-this');

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

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')->name('dashboard');

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
    Route::post('/homepage/footer', [HomepageController::class, 'updateFooter'])
        ->middleware('permission:homepage.edit')->name('homepage.footer');
    Route::post('/homepage/memorable-moments', [HomepageController::class, 'updateMemorableMoments'])
        ->middleware('permission:homepage.edit')->name('homepage.memorable-moments');

    // ── Private file serving (auth-protected) ─────────────────────────────────
    Route::get('/private/{path}', [PrivateFileController::class, 'serve'])
        ->where('path', '.+')
        ->name('private.file');

    // Overview (residents, posyandu, and any role with overview.view permission)
    Route::get('/overview', [OverviewController::class, 'index'])
        ->middleware('permission:overview.view')->name('overview');

    // ── Residents ─────────────────────────────────────────────────────────────
    Route::get('/residents', [ResidentController::class, 'index'])
        ->middleware('permission:residents.view')->name('residents.index');
    Route::post('/residents', [ResidentController::class, 'store'])
        ->middleware('permission:residents.create')->name('residents.store');
    Route::post('/residents/import-excel', [ResidentController::class, 'importExcel'])
        ->middleware('permission:residents.create')->name('residents.import');
    Route::get('/residents/{resident}/edit', [ResidentController::class, 'edit'])
        ->middleware('permission:residents.edit')->name('residents.edit');
    Route::match(['PUT', 'PATCH'], '/residents/{resident}', [ResidentController::class, 'update'])
        ->middleware('permission:residents.edit')->name('residents.update');
    Route::patch('/residents/{resident}/deactivate', [ResidentController::class, 'deactivate'])
        ->middleware('permission:residents.edit')->name('residents.deactivate');
    Route::delete('/residents/{resident}', [ResidentController::class, 'destroy'])
        ->middleware('permission:residents.delete')->name('residents.destroy');

    // ── Posyandu ───────────────────────────────────────────────────────────────
    Route::get('/posyandu', [\App\Http\Controllers\PosyanduController::class, 'index'])
        ->middleware('permission:posyandu.view')->name('posyandu.index');
    Route::get('/posyandu/export', [\App\Http\Controllers\PosyanduController::class, 'export'])
        ->middleware('permission:posyandu.view')->name('posyandu.export');

    // Family Members (nested under resident)
    Route::post('/residents/{resident}/family-members', [FamilyMemberController::class, 'store'])
        ->middleware('permission:residents.edit')->name('residents.family-members.store');
    Route::match(['PUT', 'PATCH'], '/residents/{resident}/family-members/{familyMember}', [FamilyMemberController::class, 'update'])
        ->middleware('permission:residents.edit')->name('residents.family-members.update');
    Route::delete('/residents/{resident}/family-members/{familyMember}', [FamilyMemberController::class, 'destroy'])
        ->middleware('permission:residents.edit')->name('residents.family-members.destroy');
    Route::patch('/residents/{resident}/family-members/{familyMember}/set-head', [FamilyMemberController::class, 'setHead'])
        ->middleware('permission:residents.edit')->name('residents.family-members.set-head');

    // ── Blocks ────────────────────────────────────────────────────────────────
    Route::get('/blocks', [BlockController::class, 'index'])
        ->middleware('permission:blocks.view')->name('blocks.index');
    Route::post('/blocks', [BlockController::class, 'store'])
        ->middleware('permission:blocks.create')->name('blocks.store');
    Route::post('/blocks/import-excel', [BlockController::class, 'importExcel'])
        ->middleware('permission:blocks.create')->name('blocks.import');
    Route::match(['PUT', 'PATCH'], '/blocks/{block}', [BlockController::class, 'update'])
        ->middleware('permission:blocks.edit')->name('blocks.update');
    Route::delete('/blocks/{block}', [BlockController::class, 'destroy'])
        ->middleware('permission:blocks.delete')->name('blocks.destroy');

    // ── Units (nested under Block) ─────────────────────────────────────────────
    Route::get('/blocks/{block}/units', [UnitController::class, 'index'])
        ->middleware('permission:blocks.view')->name('blocks.units.index');
    Route::post('/blocks/{block}/units', [UnitController::class, 'store'])
        ->middleware('permission:blocks.edit')->name('blocks.units.store');
    Route::match(['PUT', 'PATCH'], '/blocks/{block}/units/{unit}', [UnitController::class, 'update'])
        ->middleware('permission:blocks.edit')->name('blocks.units.update');
    Route::delete('/blocks/{block}/units/{unit}', [UnitController::class, 'destroy'])
        ->middleware('permission:blocks.edit')->name('blocks.units.destroy');

    // ── API: units by block (for resident/user form AJAX dropdown) ──────────────
    Route::get('/api/blocks/{block}/units', [ApiBlockController::class, 'units'])
        ->name('api.blocks.units');

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
    Route::post('/users/check-resident-email', [ApiResidentController::class, 'checkEmail'])
        ->middleware(['permission:users.edit', 'throttle:30,1'])->name('users.check-resident-email');
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

    // Specific sub-paths MUST be declared before the {mediaFile} wildcard
    Route::delete('/media', [MediaController::class, 'bulkDestroy'])
        ->middleware('permission:media.delete')->name('media.bulk-destroy');
    Route::delete('/media/virtual-bulk', [MediaController::class, 'virtualBulkDestroy'])
        ->middleware('permission:media.delete')->name('media.virtual-bulk-destroy');
    Route::delete('/media/resident-photo/{resident}', [MediaController::class, 'destroyResidentPhoto'])
        ->middleware('permission:media.delete')->name('media.resident-photo.destroy');
    Route::delete('/media/member-photo/{familyMember}', [MediaController::class, 'destroyMemberPhoto'])
        ->middleware('permission:media.delete')->name('media.member-photo.destroy');

    // Wildcard — must stay last so it does not swallow the routes above
    Route::delete('/media/{mediaFile}', [MediaController::class, 'destroy'])
        ->middleware('permission:media.delete')->name('media.destroy');



    // ── Sensitive data reveal (admin-only AJAX) ──────────────────────────────
    Route::get('/residents/{resident}/reveal-fcn', [SensitiveDataController::class, 'revealFCN'])
        ->middleware(['permission:residents.view', 'throttle:10,1'])
        ->name('residents.reveal-fcn');
    Route::get('/residents/{resident}/family-members/{familyMember}/reveal-nik', [SensitiveDataController::class, 'revealNIK'])
        ->middleware(['permission:residents.view', 'throttle:10,1'])
        ->name('residents.family-members.reveal-nik');

    // ── Household (resident self-service) ─────────────────────────────────
    Route::get('/household', [HouseholdController::class, 'show'])
        ->name('household.show');
    Route::match(['PUT', 'PATCH'], '/household', [HouseholdController::class, 'update'])
        ->name('household.update');
    Route::post('/household/family-members', [HouseholdController::class, 'storeFamilyMember'])
        ->name('household.family-members.store');
    Route::match(['PUT', 'PATCH'], '/household/family-members/{familyMember}', [HouseholdController::class, 'updateFamilyMember'])
        ->name('household.family-members.update');
    Route::delete('/household/family-members/{familyMember}', [HouseholdController::class, 'destroyFamilyMember'])
        ->name('household.family-members.destroy');
    Route::patch('/household/family-members/{familyMember}/set-head', [HouseholdController::class, 'setFamilyMemberHead'])
        ->name('household.family-members.set-head');

    // ── Settings (profile — all roles) ────────────────────────────────────────
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings/profile', [SettingController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/settings/password', [SettingController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/reset-link', [SettingController::class, 'sendResetLink'])->name('settings.reset-link');
    Route::post('/settings/security', [SettingController::class, 'updateSecurity'])->middleware('permission:settings.edit')->name('settings.security');
    Route::post('/settings/memo', [SettingController::class, 'updateMemo'])->middleware('permission:settings.edit')->name('settings.memo');
    Route::post('/settings/posyandu', [SettingController::class, 'updatePosyandu'])->middleware('permission:settings.edit')->name('settings.posyandu');
});
