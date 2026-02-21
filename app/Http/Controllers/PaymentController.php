<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\PaymentRecord;
use App\Models\Resident;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = PaymentRecord::with(['resident.block', 'paymentMethod', 'submittedBy'])
            ->orderByRaw("FIELD(status, 'pending', 'rejected', 'approved') ASC")
            ->orderByDesc('payment_month');

        // Search by resident name or unit
        if ($search = $request->get('search')) {
            $query->whereHas('resident', function ($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%")
                    ->orWhere('unit_number', 'like', "%{$search}%");
            });
        }

        // Block filter
        if ($blockId = $request->get('block_id')) {
            $query->whereHas('resident', fn($q) => $q->where('block_id', $blockId));
        }

        // Status filter
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Month filter
        if ($month = $request->get('month')) {
            $query->where('payment_month', 'like', $month . '%');
        }

        $payments = $query->paginate(20)->withQueryString();
        $blocks = Block::active()->orderBy('name')->get();
        $currency = Setting::get('currency_symbol', 'Rp');

        // Summary stats
        $pendingCount = PaymentRecord::where('status', 'pending')->count();
        $pendingTotal = PaymentRecord::where('status', 'pending')->sum('amount');
        $collectedMonth = PaymentRecord::where('status', 'approved')
            ->whereYear('payment_month', now()->year)
            ->whereMonth('payment_month', now()->month)
            ->sum('amount');
        $unpaidCount = Resident::where('is_active', true)
            ->whereDoesntHave(
                'paymentRecords',
                fn($q) =>
                $q->where('status', 'approved')
                    ->whereYear('payment_month', now()->year)
                    ->whereMonth('payment_month', now()->month)
            )->count();

        return view('payments', compact(
            'payments',
            'blocks',
            'currency',
            'pendingCount',
            'pendingTotal',
            'collectedMonth',
            'unpaidCount'
        ));
    }

    public function approve(PaymentRecord $payment)
    {
        $payment->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        return redirect()->route('payments.index')
            ->with('success', "Payment from {$payment->resident->fullname} has been approved.");
    }

    public function reject(Request $request, PaymentRecord $payment)
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'rejection_reason.required' => 'Please provide a reason for rejection.',
            'rejection_reason.min' => 'Rejection reason must be at least 10 characters.',
        ]);

        $payment->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return redirect()->route('payments.index')
            ->with('success', "Payment rejected and resident has been notified.");
    }
}
