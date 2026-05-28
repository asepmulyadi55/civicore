<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlockController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ResidentController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ── Auth (public, rate-limited) ──────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('api.auth.login');
});

// ── Authenticated routes ─────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::post('logout-all', [AuthController::class, 'revokeAll'])->name('api.auth.logout-all');
        Route::get('me', [AuthController::class, 'me'])->name('api.auth.me');
    });

    // Dashboard
    Route::get('dashboard/stats', [DashboardController::class, 'stats'])->name('api.dashboard.stats');

    // Residents
    Route::get('residents/{resident}/photo', [ResidentController::class, 'photo'])->name('api.residents.photo');
    Route::post('residents/check-email', [ResidentController::class, 'checkEmail'])->name('api.residents.check-email');
    Route::apiResource('residents', ResidentController::class)->names([
        'index'   => 'api.residents.index',
        'show'    => 'api.residents.show',
        'store'   => 'api.residents.store',
        'update'  => 'api.residents.update',
        'destroy' => 'api.residents.destroy',
    ]);

    // Blocks
    Route::get('blocks/{block}/units', [BlockController::class, 'units'])->name('api.blocks.units');
    Route::apiResource('blocks', BlockController::class)->names([
        'index'   => 'api.blocks.index',
        'show'    => 'api.blocks.show',
        'store'   => 'api.blocks.store',
        'update'  => 'api.blocks.update',
        'destroy' => 'api.blocks.destroy',
    ]);

    // Payments
    Route::get('payments/{payment}/proof', [PaymentController::class, 'proof'])->name('api.payments.proof');
    Route::post('payments/upload-proof', [PaymentController::class, 'uploadProof'])->name('api.payments.upload-proof');
    Route::post('payments/{payment}/review', [PaymentController::class, 'review'])
        ->middleware('api.permission:payments.approve')
        ->name('api.payments.review');
    Route::get('payments', [PaymentController::class, 'index'])->name('api.payments.index');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('api.payments.show');

    // Reports
    Route::prefix('reports')->name('api.reports.')->group(function () {
        Route::get('finance-summary', [ReportController::class, 'financeSummary'])->name('finance-summary');
        Route::get('monthly', [ReportController::class, 'monthlyReport'])->name('monthly');
        Route::get('finance', [ReportController::class, 'financeReports'])->name('finance');
    });

    // Users & Roles
    Route::get('roles', [UserController::class, 'roles'])->name('api.roles.index');
    Route::post('users/{user}/assign-role', [UserController::class, 'assignRole'])->name('api.users.assign-role');
    Route::apiResource('users', UserController::class)->names([
        'index'   => 'api.users.index',
        'show'    => 'api.users.show',
        'store'   => 'api.users.store',
        'update'  => 'api.users.update',
        'destroy' => 'api.users.destroy',
    ]);

    // Media
    Route::get('media/{media}/file', [MediaController::class, 'file'])->name('api.media.file');
    Route::post('media/upload', [MediaController::class, 'upload'])->name('api.media.upload');
    Route::get('media', [MediaController::class, 'index'])->name('api.media.index');
    Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('api.media.destroy');

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('api.settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('api.settings.update');
});
