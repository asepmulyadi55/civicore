<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\PaymentRecord;
use App\Models\Resident;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
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

    // Notification: pending payment batches (up to 5 unique batches)
    $rawPendingPayments = PaymentRecord::with(['resident'])
      ->where('status', 'pending')
      ->when($scopeBlockId, fn($q) => $q->whereHas('resident', fn($r) => $r->where('block_id', $scopeBlockId)))
      ->orderByDesc('created_at')
      ->limit(30)
      ->get();

    $notifPayments = $rawPendingPayments
      ->groupBy(fn($r) => $r->batch_id ?? 'single_' . $r->id)
      ->map(function ($records) {
        $lead = $records->first();
        $lead->notif_month_count = $records->count();
        return $lead;
      })
      ->values()
      ->take(5);

    // Notification: pending user registrations (admin only)
    $notifUsers = $user->isAdmin()
      ? User::where('is_active', false)->whereNotNull('email')->orderByDesc('created_at')->limit(5)->get()
      : collect();

    $notifTotal = $notifPayments->count() + $notifUsers->count();

    // Badge count: only items newer than the last time the user opened the panel
    $notifReadAt = session('notif_read_at') ? Carbon::parse(session('notif_read_at')) : null;
    if ($notifReadAt) {
      $newPayments = $rawPendingPayments
        ->filter(fn($p) => $p->created_at > $notifReadAt)
        ->groupBy(fn($r) => $r->batch_id ?? 'single_' . $r->id)
        ->count();
      $newUsers = $notifUsers->filter(fn($u) => $u->created_at > $notifReadAt)->count();
      $notifBadge = $newPayments + $newUsers;
    } else {
      $notifBadge = $notifTotal;
    }

    return view('dashboard', compact(
      'currency',
      'totalCollected',
      'pendingCount',
      'unpaidCount',
      'activeResidents',
      'recentActivity',
      'notifPayments',
      'notifUsers',
      'notifTotal',
      'notifBadge'
    ));
  }

  public function markNotificationsRead(): JsonResponse
  {
    session(['notif_read_at' => now()->toISOString()]);
    return response()->json(['ok' => true]);
  }
}
