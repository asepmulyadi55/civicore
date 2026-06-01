<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\HouseholderController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\FinanceController;
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
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\SensitiveDataController;
use App\Http\Controllers\Api\BlockController as ApiBlockController;
use App\Http\Controllers\Api\HouseholderController as ApiHouseholderController;
use App\Http\Controllers\Api\HomepageController as ApiHomepageController;
use App\Http\Controllers\PropertyListingController;

// ── Public homepage (React SPA) ───────────────────────────────────────────────
Route::get('/', fn() => view('spa'))->name('home');
Route::get('/events', fn() => view('spa'))->name('events');
Route::get('/buletin', fn() => view('spa'))->name('buletin');
Route::get('/property', fn() => view('spa'))->name('property');
Route::get('/property/{id}', fn() => view('spa'))->name('property.detail');

// ── Sitemap ───────────────────────────────────────────────────────────────────
Route::get('/sitemap.xml', function () {
    $content = view('sitemap')->render();
    return response($content, 200)
        ->header('Content-Type', 'application/xml');
})->name('sitemap');

// ── Internal API — Homepage content for React SPA ───────────────────────────
// Protected by X-Api-Key header (key injected into SPA via Blade meta tag).
Route::get('/api/homepage', [ApiHomepageController::class, 'index'])
    ->middleware('api.key')
    ->name('api.homepage');
Route::get('/api/events', [ApiHomepageController::class, 'events'])
    ->middleware('api.key')
    ->name('api.events');
Route::get('/api/buletin', [ApiHomepageController::class, 'buletin'])
    ->middleware('api.key')
    ->name('api.buletin');
Route::get('/api/property', [ApiHomepageController::class, 'property'])
    ->middleware('api.key')
    ->name('api.property');
Route::get('/api/property/{id}', [ApiHomepageController::class, 'propertyDetail'])
    ->middleware('api.key')
    ->name('api.property.detail');

// ── Auth (public) ─────────────────────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1');
Route::get('/logout', fn() => redirect()->route('login'));
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
    Route::post('/dashboard/notifications/read', [DashboardController::class, 'markNotificationsRead'])
        ->name('notifications.read');

    // ── Property Listings ─────────────────────────────────────────────────────
    Route::get('/property-listings', [PropertyListingController::class, 'index'])
        ->middleware('permission:property.view')->name('property.index');
    Route::post('/property-listings', [PropertyListingController::class, 'store'])
        ->middleware('permission:property.create')->name('property.store');
    Route::put('/property-listings/{property}', [PropertyListingController::class, 'update'])
        ->middleware('permission:property.edit')->name('property.update');
    Route::delete('/property-listings/{property}', [PropertyListingController::class, 'destroy'])
        ->middleware('permission:property.delete')->name('property.destroy');
    Route::patch('/property-listings/{property}/toggle-active', [PropertyListingController::class, 'toggleActive'])
        ->middleware('permission:property.edit')->name('property.toggle-active');

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
    Route::post('/homepage/buletin', [HomepageController::class, 'storeBuletin'])
        ->middleware('permission:homepage.create')->name('homepage.buletin.store');
    Route::put('/homepage/buletin/{id}', [HomepageController::class, 'updateBuletin'])
        ->middleware('permission:homepage.edit')->name('homepage.buletin.update');
    Route::delete('/homepage/buletin/{id}', [HomepageController::class, 'destroyBuletin'])
        ->middleware('permission:homepage.delete')->name('homepage.buletin.destroy');
    Route::post('/homepage/about', [HomepageController::class, 'updateAbout'])
        ->middleware('permission:homepage.edit')->name('homepage.about');
    Route::post('/homepage/section-labels', [HomepageController::class, 'updateSectionLabels'])
        ->middleware('permission:homepage.edit')->name('homepage.section-labels');
    Route::post('/homepage/footer', [HomepageController::class, 'updateFooter'])
        ->middleware('permission:homepage.edit')->name('homepage.footer');
    Route::post('/homepage/memorable-moments', [HomepageController::class, 'updateMemorableMoments'])
        ->middleware('permission:homepage.edit')->name('homepage.memorable-moments');
    Route::post('/homepage/metadata', [HomepageController::class, 'updateMetadata'])
        ->middleware('permission:homepage.edit')->name('homepage.metadata');

    // ── Private file serving (auth-protected) ─────────────────────────────────
    Route::get('/private/{path}', [PrivateFileController::class, 'serve'])
        ->where('path', '.+')
        ->name('private.file');

    // Overview (residents, posyandu, and any role with overview.view permission)
    Route::get('/overview', [OverviewController::class, 'index'])
        ->middleware('permission:overview.view')->name('overview');

    // ── Householders ──────────────────────────────────────────────────────────
    Route::get('/householders', [HouseholderController::class, 'index'])
        ->middleware('permission:householders.view')->name('householders.index');
    Route::post('/householders', [HouseholderController::class, 'store'])
        ->middleware('permission:householders.create')->name('householders.store');
    Route::post('/householders/import-excel', [HouseholderController::class, 'importExcel'])
        ->middleware('permission:householders.create')->name('householders.import');
    Route::get('/householders/{householder}/edit', [HouseholderController::class, 'edit'])
        ->middleware('permission:householders.edit')->name('householders.edit');
    Route::match(['PUT', 'PATCH'], '/householders/{householder}', [HouseholderController::class, 'update'])
        ->middleware('permission:householders.edit')->name('householders.update');
    Route::patch('/householders/{householder}/deactivate', [HouseholderController::class, 'deactivate'])
        ->middleware('permission:householders.edit')->name('householders.deactivate');
    Route::delete('/householders/{householder}', [HouseholderController::class, 'destroy'])
        ->middleware('permission:householders.delete')->name('householders.destroy');

    // ── Posyandu ───────────────────────────────────────────────────────────────
    Route::get('/posyandu', [\App\Http\Controllers\PosyanduController::class, 'index'])
        ->middleware('permission:posyandu.view')->name('posyandu.index');
    Route::get('/posyandu/export', [\App\Http\Controllers\PosyanduController::class, 'export'])
        ->middleware('permission:posyandu.view')->name('posyandu.export');

    // Residents (nested under householder)
    Route::post('/householders/{householder}/residents', [ResidentController::class, 'store'])
        ->middleware('permission:householders.edit')->name('householders.residents.store');
    Route::match(['PUT', 'PATCH'], '/householders/{householder}/residents/{resident}', [ResidentController::class, 'update'])
        ->middleware('permission:householders.edit')->name('householders.residents.update');
    Route::delete('/householders/{householder}/residents/{resident}', [ResidentController::class, 'destroy'])
        ->middleware('permission:householders.edit')->name('householders.residents.destroy');
    Route::patch('/householders/{householder}/residents/{resident}/set-head', [ResidentController::class, 'setHead'])
        ->middleware('permission:householders.edit')->name('householders.residents.set-head');

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

    // ── Finance ───────────────────────────────────────────────────────────────
    Route::get('/finance', [FinanceController::class, 'index'])
        ->middleware('permission:finance.view')->name('finance.index');
    Route::post('/finance/transactions', [FinanceController::class, 'storeTransaction'])
        ->middleware('permission:finance.create')->name('finance.transactions.store');
    Route::put('/finance/transactions/{transaction}', [FinanceController::class, 'updateTransaction'])
        ->middleware('permission:finance.edit')->name('finance.transactions.update');
    Route::delete('/finance/transactions/{transaction}', [FinanceController::class, 'destroyTransaction'])
        ->middleware('permission:finance.delete')->name('finance.transactions.destroy');
    Route::post('/finance/reports/generate', [FinanceController::class, 'generateReport'])
        ->middleware('permission:finance.create')->name('finance.reports.generate');
    Route::patch('/finance/reports/{report}/opening-balance', [FinanceController::class, 'updateOpeningBalance'])
        ->middleware('permission:finance.edit')->name('finance.reports.opening-balance');
    Route::patch('/finance/reports/{report}/submit', [FinanceController::class, 'submitReport'])
        ->middleware('permission:finance.create')->name('finance.reports.submit');
    Route::patch('/finance/reports/{report}/approve', [FinanceController::class, 'approveReport'])
        ->middleware('permission:finance.approve')->name('finance.reports.approve');
    Route::patch('/finance/reports/{report}/reject', [FinanceController::class, 'rejectReport'])
        ->middleware('permission:finance.approve')->name('finance.reports.reject');
    Route::patch('/finance/reports/{report}/revise', [FinanceController::class, 'reviseReport'])
        ->middleware('permission:finance.approve')->name('finance.reports.revise');
    Route::get('/finance/reports/{report}/export', [FinanceController::class, 'exportReport'])
        ->middleware('permission:finance.view')->name('finance.reports.export');
    Route::delete('/finance/reports/{report}', [FinanceController::class, 'destroyReport'])
        ->middleware('permission:finance.delete')->name('finance.reports.destroy');
    Route::get('/finance/categories/search', [FinanceController::class, 'searchCategories'])
        ->middleware(['permission:finance.create', 'throttle:60,1'])->name('finance.categories.search');

    // ── User Management ───────────────────────────────────────────────────────
    Route::get('/users', [UserController::class, 'index'])
        ->middleware('permission:users.view')->name('users.index');
    Route::post('/users', [UserController::class, 'store'])
        ->middleware('permission:users.create')->name('users.store');
    Route::match(['PUT', 'PATCH'], '/users/{user}', [UserController::class, 'update'])
        ->middleware('permission:users.edit')->name('users.update');
    Route::post('/users/{user}/approve', [UserController::class, 'approve'])
        ->middleware('permission:users.approve')->name('users.approve');
    Route::post('/users/check-resident-email', [ApiHouseholderController::class, 'checkEmail'])
        ->middleware(['permission:users.edit', 'throttle:30,1'])->name('users.check-resident-email');
    Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate'])
        ->middleware('permission:users.edit')->name('users.deactivate');
    Route::patch('/users/{user}/reactivate', [UserController::class, 'reactivate'])
        ->middleware('permission:users.edit')->name('users.reactivate');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:users.delete')->name('users.destroy');


    // ── Organization ─────────────────────────────────────────────────────────
    // View: all authenticated users. Manage (create/edit/delete): admin only (checked in controller)
    Route::get('/organization', [OrganizationController::class, 'index'])->name('organization.index');
    Route::post('/organization/periods', [OrganizationController::class, 'storePeriod'])->name('organization.periods.store');
    Route::put('/organization/periods/{period}', [OrganizationController::class, 'updatePeriod'])->name('organization.periods.update');
    Route::patch('/organization/periods/{period}/activate', [OrganizationController::class, 'activatePeriod'])->name('organization.periods.activate');
    Route::delete('/organization/periods/{period}', [OrganizationController::class, 'destroyPeriod'])->name('organization.periods.destroy');
    Route::post('/organization/periods/{period}/positions', [OrganizationController::class, 'storePosition'])->name('organization.positions.store');
    Route::put('/organization/positions/{position}', [OrganizationController::class, 'updatePosition'])->name('organization.positions.update');
    Route::delete('/organization/positions/{position}', [OrganizationController::class, 'destroyPosition'])->name('organization.positions.destroy');
    Route::get('/organization/search-members', [OrganizationController::class, 'searchMembers'])
        ->middleware('throttle:60,1')->name('organization.search-members');

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
    Route::delete('/media/householder-photo/{householder}', [MediaController::class, 'destroyHouseholderPhoto'])
        ->middleware('permission:media.delete')->name('media.householder-photo.destroy');
    Route::delete('/media/resident-photo/{resident}', [MediaController::class, 'destroyResidentPhoto'])
        ->middleware('permission:media.delete')->name('media.resident-photo.destroy');

    // Wildcard — must stay last so it does not swallow the routes above
    Route::delete('/media/{mediaFile}', [MediaController::class, 'destroy'])
        ->middleware('permission:media.delete')->name('media.destroy');



    // ── Sensitive data reveal (admin-only AJAX) ──────────────────────────────
    Route::get('/householders/{householder}/reveal-fcn', [SensitiveDataController::class, 'revealFCN'])
        ->middleware(['permission:householders.view', 'throttle:10,1'])
        ->name('householders.reveal-fcn');
    Route::get('/householders/{householder}/residents/{resident}/reveal-nik', [SensitiveDataController::class, 'revealNIK'])
        ->middleware(['permission:householders.view', 'throttle:10,1'])
        ->name('householders.residents.reveal-nik');

    // ── Household (self-service) ───────────────────────────────────────────────
    Route::get('/household', [HouseholdController::class, 'show'])
        ->name('household.show');
    Route::match(['PUT', 'PATCH'], '/household', [HouseholdController::class, 'update'])
        ->name('household.update');
    Route::post('/household/residents', [HouseholdController::class, 'storeResident'])
        ->name('household.residents.store');
    Route::match(['PUT', 'PATCH'], '/household/residents/{resident}', [HouseholdController::class, 'updateResident'])
        ->name('household.residents.update');
    Route::delete('/household/residents/{resident}', [HouseholdController::class, 'destroyResident'])
        ->name('household.residents.destroy');
    Route::patch('/household/residents/{resident}/set-head', [HouseholdController::class, 'setResidentHead'])
        ->name('household.residents.set-head');

    // ── Settings (profile — all roles) ────────────────────────────────────────
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings/profile', [SettingController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/settings/password', [SettingController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/reset-link', [SettingController::class, 'sendResetLink'])->name('settings.reset-link');
    Route::post('/settings/security', [SettingController::class, 'updateSecurity'])->middleware('permission:settings.edit')->name('settings.security');
    Route::post('/settings/memo', [SettingController::class, 'updateMemo'])->middleware('permission:settings.edit')->name('settings.memo');
    Route::post('/settings/posyandu', [SettingController::class, 'updatePosyandu'])->middleware('permission:settings.edit')->name('settings.posyandu');
});
