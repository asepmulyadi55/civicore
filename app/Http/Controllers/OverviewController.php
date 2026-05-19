<?php

namespace App\Http\Controllers;

use App\Models\PaymentRecord;
use App\Models\Resident;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OverviewController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Primary: find resident linked by user_id
        $resident = $user->resident()
            ->with(['block', 'feeHistories' => fn($q) => $q->orderByDesc('effective_from')])
            ->first();

        // Fallback 1: find by email (covers the case where resident was created
        // after the user was already approved, so user_id was never set)
        if (!$resident && $user->email) {
            $resident = Resident::where('email', $user->email)
                ->with(['block', 'feeHistories' => fn($q) => $q->orderByDesc('effective_from')])
                ->first();

            // Auto-repair the link so future lookups use the fast path
            if ($resident) {
                $resident->update(['user_id' => $user->id]);
            }
        }

        // Fallback 2: find by block_id + unit_number (covers admin-assigned accounts
        // where neither user_id nor email was matched to a resident record)
        if (!$resident && $user->block_id && $user->unit_number) {
            $resident = Resident::whereHas('unit', fn($q) =>
                    $q->where('block_id', $user->block_id)
                      ->where('unit_number', $user->unit_number)
                )
                ->with(['block', 'feeHistories' => fn($q) => $q->orderByDesc('effective_from')])
                ->first();

            // Auto-repair so next visit uses the fast path
            if ($resident) {
                $resident->update(['user_id' => $user->id]);
            }
        }

        if (!$resident) {
            return view('overview', [
                'resident'        => null,
                'currentFee'      => 0,
                'currentYear'     => now()->year,
                'previousYear'    => now()->year - 1,
                'currentRecords'  => collect(),
                'previousRecords' => collect(),
                'totalPaidYear'   => 0,
                'paidMonthsYear'  => 0,
                'currency'        => Setting::get('currency_symbol', 'Rp'),
                'dueDayLabel'     => Setting::get('payment_due_day', '10'),
            ]);
        }

        $currentYear = now()->year;
        $previousYear = $currentYear - 1;
        $currency = Setting::get('currency_symbol', 'Rp');
        $dueDayLabel = Setting::get('payment_due_day', '10');

        // Current fee from most recent fee history (currentFee() returns FeeHistory model)
        $currentFeeHistory = $resident->currentFee();
        $currentFee = $currentFeeHistory?->amount ?? 0;

        // Payment records for current year (Jan–Dec), keyed by month number
        $currentRecords = PaymentRecord::where('resident_id', $resident->id)
            ->whereYear('payment_month', $currentYear)
            ->orderBy('payment_month')
            ->get()
            ->keyBy(fn($r) => Carbon::parse($r->payment_month)->month);

        // Payment records for previous year
        $previousRecords = PaymentRecord::where('resident_id', $resident->id)
            ->whereYear('payment_month', $previousYear)
            ->orderBy('payment_month')
            ->get()
            ->keyBy(fn($r) => Carbon::parse($r->payment_month)->month);

        // Summary stats for current year
        $totalPaidYear = $currentRecords->where('status', 'approved')->sum('amount');
        $paidMonthsYear = $currentRecords->where('status', 'approved')->count();

        return view('overview', compact(
            'resident',
            'currentFee',
            'currentYear',
            'previousYear',
            'currentRecords',
            'previousRecords',
            'totalPaidYear',
            'paidMonthsYear',
            'currency',
            'dueDayLabel'
        ));
    }
}
