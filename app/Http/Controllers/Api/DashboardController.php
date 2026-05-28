<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\PaymentRecord;
use App\Models\Resident;
use App\Models\User;
use App\Enums\PaymentStatus;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    public function stats(Request $request): JsonResponse
    {
        if (!$request->user()->can('dashboard.view')) {
            return $this->forbidden();
        }

        $currentMonth = now()->month;
        $currentYear  = now()->year;

        $stats = [
            'residents' => [
                'total'  => Resident::count(),
                'active' => Resident::where('is_active', true)->count(),
            ],
            'blocks' => [
                'total'  => Block::count(),
                'active' => Block::where('is_active', true)->count(),
            ],
            'users' => [
                'total'  => User::count(),
                'active' => User::where('is_active', true)->count(),
            ],
            'payments' => [
                'this_month' => [
                    'approved' => PaymentRecord::whereYear('payment_month', $currentYear)
                        ->whereMonth('payment_month', $currentMonth)
                        ->where('status', PaymentStatus::Approved)
                        ->sum('amount'),
                    'pending_count' => PaymentRecord::whereYear('payment_month', $currentYear)
                        ->whereMonth('payment_month', $currentMonth)
                        ->where('status', PaymentStatus::Pending)
                        ->count(),
                ],
            ],
        ];

        return $this->success($stats, 'Dashboard stats fetched successfully');
    }
}
