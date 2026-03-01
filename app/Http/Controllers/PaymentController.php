<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\PaymentRecord;
use App\Models\Resident;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $scopeBlockId = $user->isBlockCoordinator() ? $user->block_id : null;
        $canApprove = $user->canApprovePayments();

        $query = PaymentRecord::with(['resident.block', 'paymentMethod', 'submittedBy'])
            ->orderByRaw("FIELD(status, 'pending', 'rejected', 'approved') ASC")
            ->orderByDesc('payment_month');

        // Scope to coordinator's block
        if ($scopeBlockId) {
            $query->whereHas('resident', fn($q) => $q->where('block_id', $scopeBlockId));
        }

        // Search by resident name or unit
        if ($search = $request->get('search')) {
            $query->whereHas('resident', function ($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%")
                    ->orWhere('unit_number', 'like', "%{$search}%");
            });
        }

        // Block filter (only for non-coordinators)
        if (!$scopeBlockId && $blockId = $request->get('block_id')) {
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

        // Residents for the Add/Edit modal dropdowns — scoped to coordinator's block
        $residents = Resident::with('block')
            ->where('is_active', true)
            ->when($scopeBlockId, fn($q) => $q->where('block_id', $scopeBlockId))
            ->orderBy('fullname')
            ->get();

        // Summary stats — scoped to coordinator's block when applicable
        $statBase = PaymentRecord::when(
            $scopeBlockId,
            fn($q) => $q->whereHas('resident', fn($r) => $r->where('block_id', $scopeBlockId))
        );
        $pendingCount = (clone $statBase)->where('status', 'pending')->count();
        $pendingTotal = (clone $statBase)->where('status', 'pending')->sum('amount');
        $collectedMonth = (clone $statBase)->where('status', 'approved')
            ->whereYear('payment_month', now()->year)
            ->whereMonth('payment_month', now()->month)
            ->sum('amount');
        $unpaidCount = Resident::where('is_active', true)
            ->when($scopeBlockId, fn($q) => $q->where('block_id', $scopeBlockId))
            ->whereDoesntHave(
                'paymentRecords',
                fn($q) => $q->where('status', 'approved')
                    ->whereYear('payment_month', now()->year)
                    ->whereMonth('payment_month', now()->month)
            )->count();

        // Build paid-months map for JS month grid: residentId → ['YYYY-MM', ...]
        $paidMonthsByResident = PaymentRecord::whereIn('status', ['pending', 'approved'])
            ->when(
                $scopeBlockId,
                fn($q) => $q->whereHas('resident', fn($r) => $r->where('block_id', $scopeBlockId))
            )
            ->get(['resident_id', 'payment_month'])
            ->groupBy('resident_id')
            ->map(fn($rows) => $rows->map(
                fn($r) => \Carbon\Carbon::parse($r->payment_month)->format('Y-m')
            )->values()->all());

        return view('payments', compact(
            'payments',
            'blocks',
            'currency',
            'pendingCount',
            'pendingTotal',
            'collectedMonth',
            'unpaidCount',
            'paidMonthsByResident',
            'canApprove',
            'residents'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'resident_id' => ['required', 'exists:residents,id'],
            'months' => ['required', 'array', 'min:1'],
            'months.*' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'status' => ['required', 'in:unpaid,pending,approved'],
            'proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('proofs', 'public');
        }

        $baseData = [
            'resident_id' => $request->resident_id,
            'amount' => $request->amount,
            'payment_method_id' => $request->payment_method_id ?: null,
            'status' => $request->status,
            'notes' => $request->notes,
            'proof_path' => $proofPath,
            'submitted_by' => auth()->id(),
        ];

        if ($baseData['status'] === 'approved') {
            $baseData['approved_by'] = auth()->id();
            $baseData['approved_at'] = now();
        }

        $created = 0;
        $skipped = 0;
        $batchId = (string) Str::uuid(); // shared across all months in this submission
        foreach ($request->months as $monthStr) {
            $paymentMonth = $monthStr . '-01';
            $exists = PaymentRecord::where('resident_id', $request->resident_id)
                ->where('payment_month', $paymentMonth)
                ->exists();
            if ($exists) {
                $skipped++;
                continue;
            }
            PaymentRecord::create(array_merge($baseData, [
                'payment_month' => $paymentMonth,
                'batch_id' => $batchId,
            ]));
            $created++;
        }

        $msg = "Created {$created} payment record" . ($created !== 1 ? 's' : '') . '.';
        if ($skipped)
            $msg .= " {$skipped} skipped (already exist).";

        return redirect()->route('payments.index')->with('success', $msg);
    }

    public function update(Request $request, PaymentRecord $payment)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_month' => ['required', 'string'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'status' => ['required', 'in:unpaid,pending,approved,rejected'],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $data['payment_month'] = $data['payment_month'] . '-01';

        // Proof replacement
        if ($request->hasFile('proof')) {
            // Delete old file
            if ($payment->proof_path) {
                \Storage::disk('public')->delete($payment->proof_path);
            }
            $data['proof_path'] = $request->file('proof')->store('proofs', 'public');
        }
        unset($data['proof']);

        // Re-stamp approval if now approved
        if ($data['status'] === 'approved' && $payment->status !== 'approved') {
            $data['approved_by'] = auth()->id();
            $data['approved_at'] = now();
        } elseif ($data['status'] !== 'approved') {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }

        // Clear rejection reason if status is not rejected
        if ($data['status'] !== 'rejected') {
            $data['rejection_reason'] = null;
        }

        $payment->update($data);

        return redirect()->route('payments.index')
            ->with('success', 'Payment updated successfully.');
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

    // ── Batch approve / reject ─────────────────────────────────────────
    public function approveBatch(string $batchId)
    {
        $records = PaymentRecord::where('batch_id', $batchId)
            ->where('status', 'pending')
            ->get();

        $count = $records->count();
        if ($count === 0) {
            return redirect()->route('payments.index')
                ->with('info', 'No pending records found in this batch.');
        }

        $records->each->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        $name = $records->first()->resident->fullname ?? 'Resident';
        return redirect()->route('payments.index')
            ->with('success', "{$count} payment(s) from {$name} approved.");
    }

    public function rejectBatch(Request $request, string $batchId)
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'rejection_reason.required' => 'Please provide a reason for rejection.',
            'rejection_reason.min' => 'Rejection reason must be at least 10 characters.',
        ]);

        $records = PaymentRecord::where('batch_id', $batchId)->get();
        $count = $records->count();

        $records->each->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        $name = $records->first()?->resident->fullname ?? 'Resident';
        return redirect()->route('payments.index')
            ->with('success', "{$count} payment(s) from {$name} rejected.");
    }

    public function destroy(PaymentRecord $payment)
    {
        // Only admin can delete payments
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('payments.index')
                ->with('error', 'Only administrators can delete payments.');
        }

        // Protect approved payments — they are financial records
        if ($payment->status === 'approved') {
            return redirect()->route('payments.index')
                ->with('error', 'Approved payments cannot be deleted. They are part of the financial record.');
        }

        // If part of a batch, delete the entire batch together
        $count = 1;
        $name = $payment->resident->fullname ?? 'Resident';
        if ($payment->batch_id) {
            $count = PaymentRecord::where('batch_id', $payment->batch_id)->count();
            PaymentRecord::where('batch_id', $payment->batch_id)->delete();
        } else {
            $payment->delete();
        }

        return redirect()->route('payments.index')
            ->with('success', "{$count} payment record(s) for {$name} deleted.");
    }
}
