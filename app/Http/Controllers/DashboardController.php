<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\PaymentRecord;
use App\Models\Resident;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class DashboardController extends Controller
{
  public function index(): View
  {
    $user = auth()->user();
    $scopeBlockId = $user->isBlockCoordinator() ? $user->block_id : null;
    $currency = Setting::get('currency_symbol', 'Rp');

    $totalCollected = PaymentRecord::where('status', 'approved')
      ->whereYear('payment_month', now()->year)
      ->whereMonth('payment_month', now()->month)
      ->when($scopeBlockId, fn($q) => $q->whereHas('resident', fn($r) => $r->where('block_id', $scopeBlockId)))
      ->sum('amount');

    $pendingCount = PaymentRecord::where('status', 'pending')
      ->when($scopeBlockId, fn($q) => $q->whereHas('resident', fn($r) => $r->where('block_id', $scopeBlockId)))
      ->count();

    $unpaidCount = Resident::where('is_active', true)
      ->when($scopeBlockId, fn($q) => $q->where('block_id', $scopeBlockId))
      ->whereDoesntHave(
        'paymentRecords',
        fn($q) => $q->where('status', 'approved')
          ->whereYear('payment_month', now()->year)
          ->whereMonth('payment_month', now()->month)
      )->count();

    $activeResidents = Resident::where('is_active', true)
      ->when($scopeBlockId, fn($q) => $q->where('block_id', $scopeBlockId))
      ->count();

    // Recent activity: last 7 batches (grouped by batch_id), scoped to block for coordinator
    $rawActivity = PaymentRecord::with(['resident.block'])
      ->whereIn('status', ['pending', 'approved', 'rejected'])
      ->when($scopeBlockId, fn($q) => $q->whereHas('resident', fn($r) => $r->where('block_id', $scopeBlockId)))
      ->orderByDesc('updated_at')
      ->limit(50)
      ->get();

    $recentActivity = $rawActivity
      ->groupBy(fn($r) => $r->batch_id ?? 'single_' . $r->id)
      ->map(function ($records) {
        $lead = $records->sortBy('payment_month')->first();
        $lead->all_months = $records->pluck('payment_month')->sort()->values();
        $lead->total_amount = $records->sum('amount');
        $lead->month_count = $records->count();
        return $lead;
      })
      ->sortByDesc('updated_at')
      ->take(7)
      ->values();

    return view('dashboard', compact(
      'currency',
      'totalCollected',
      'pendingCount',
      'unpaidCount',
      'activeResidents',
      'recentActivity'
    ));
  }
}
