<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\PaymentRecord;
use App\Models\Resident;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $scopeBlockId = $user->isBlockCoordinator() ? $user->block_id : null;
        $canApprove = $user->canApprovePayments();

        // Build base query scoped to coordinator's block if applicable
        $baseQ = PaymentRecord::query()
            ->when($scopeBlockId, fn($q) => $q->whereHas('resident', fn($r) => $r->where('block_id', $scopeBlockId)));

        // Apply search, block, status, and month filters to base query
        if ($search = $request->get('search')) {
            $baseQ->whereHas('resident', function ($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%")
                    ->orWhere('unit_number', 'like', "%{$search}%");
            });
        }
        if (!$scopeBlockId && $blockId = $request->get('block_id')) {
            $baseQ->whereHas('resident', fn($q) => $q->where('block_id', $blockId));
        }
        if ($status = $request->get('status')) {
            $baseQ->where('status', $status);
        }
        if ($month = $request->get('month')) {
            $baseQ->where('payment_month', 'like', $month . '%');
        }

        // Fetch all matching records and group in PHP by batch_key
        // This collapses multi-month batches into a single "lead" record
        // with extra virtual properties: all_months (array), total_amount, month_count
        $allRecords = $baseQ
            ->with(['resident.block', 'paymentMethod', 'submittedBy'])
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'rejected' THEN 1 ELSE 2 END ASC")
            ->orderByDesc('payment_month')
            ->get();

        // Group by batch_key (batch_id if present, else cast id as string)
        $batchGroups = $allRecords->groupBy(fn($p) => $p->batch_id ?? (string) $p->id);

        // For each batch, take the lead record and attach virtual properties
        $flatRows = $batchGroups->map(function ($records) {
            /** @var \App\Models\PaymentRecord $lead */
            $lead = $records->sortBy('payment_month')->first(); // earliest month as lead
            $lead->all_months = $records->pluck('payment_month')->sort()->values();
            $lead->total_amount = $records->sum('amount');
            $lead->month_count = $records->count();
            $lead->all_ids = $records->pluck('id')->all();
            return $lead;
        })->values();

        // Manually paginate the flat rows
        $perPage = 20;
        $page = $request->get('page', 1);
        $total = $flatRows->count();
        $items = $flatRows->slice(($page - 1) * $perPage, $perPage)->values();
        $payments = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $blocks = Block::active()->orderBy('name')->get();
        $currency = Setting::get('currency_symbol', 'Rp');

        $residents = Resident::with(['block', 'feeHistories'])
            ->where('is_active', true)
            ->when($scopeBlockId, fn($q) => $q->where('block_id', $scopeBlockId))
            ->orderBy('fullname')
            ->get();

        // Build a residentId → current fee amount map for the JS modal
        $residentFees = $residents->mapWithKeys(fn($r) => [
            $r->id => (float) ($r->currentFee()?->amount ?? 0)
        ]);

        // Summary stats based on raw records
        $statBase = PaymentRecord::when($scopeBlockId, fn($q) => $q->whereHas('resident', fn($r) => $r->where('block_id', $scopeBlockId)));
        $pendingCount = (clone $statBase)->where('status', 'pending')->count();
        $pendingTotal = (clone $statBase)->where('status', 'pending')->sum('amount');
        $collectedMonth = (clone $statBase)->where('status', 'approved')
            ->whereYear('payment_month', now()->year)->whereMonth('payment_month', now()->month)->sum('amount');
        $unpaidCount = Resident::where('is_active', true)
            ->when($scopeBlockId, fn($q) => $q->where('block_id', $scopeBlockId))
            ->whereDoesntHave('paymentRecords', fn($q) => $q->where('status', 'approved')
                ->whereYear('payment_month', now()->year)->whereMonth('payment_month', now()->month))
            ->count();

        $paidMonthsByResident = PaymentRecord::where('status', 'approved')
            ->when($scopeBlockId, fn($q) => $q->whereHas('resident', fn($r) => $r->where('block_id', $scopeBlockId)))
            ->get(['resident_id', 'payment_month'])
            ->groupBy('resident_id')
            ->map(fn($rows) => $rows->map(fn($r) => Carbon::parse($r->payment_month)->format('Y-m'))->values()->all())
            ->toArray();

        $pendingMonthsByResident = PaymentRecord::where('status', 'pending')
            ->when($scopeBlockId, fn($q) => $q->whereHas('resident', fn($r) => $r->where('block_id', $scopeBlockId)))
            ->get(['resident_id', 'payment_month'])
            ->groupBy('resident_id')
            ->map(fn($rows) => $rows->map(fn($r) => Carbon::parse($r->payment_month)->format('Y-m'))->values()->all())
            ->toArray();

        return view('payments', compact(
            'payments',
            'blocks',
            'currency',
            'pendingCount',
            'pendingTotal',
            'collectedMonth',
            'unpaidCount',
            'paidMonthsByResident',
            'pendingMonthsByResident',
            'canApprove',
            'residents',
            'residentFees'
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
        $request->validate([
            'months' => ['required', 'array', 'min:1'],
            'months.*' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'status' => ['required', 'in:unpaid,pending,approved,rejected'],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        // Handle proof replacement
        $proofPath = $payment->proof_path;
        if ($request->hasFile('proof')) {
            if ($proofPath)
                \Storage::disk('public')->delete($proofPath);
            $proofPath = $request->file('proof')->store('proofs', 'public');
        }

        $status = $request->status;
        $approvedBy = $payment->approved_by;
        $approvedAt = $payment->approved_at;
        if ($status === 'approved' && $payment->status !== 'approved') {
            $approvedBy = auth()->id();
            $approvedAt = now();
        } elseif ($status !== 'approved') {
            $approvedBy = null;
            $approvedAt = null;
        }

        $baseData = [
            'amount' => $request->amount,
            'payment_method_id' => $request->payment_method_id ?: null,
            'status' => $status,
            'rejection_reason' => $status === 'rejected' ? $request->rejection_reason : null,
            'notes' => $request->notes,
            'proof_path' => $proofPath,
            'approved_by' => $approvedBy,
            'approved_at' => $approvedAt,
        ];

        $months = $request->months;
        $batchId = $payment->batch_id ?? (string) Str::uuid();

        // Remove all old sibling records from this batch (except the lead)
        // so we start fresh with exactly the months the user selected
        if ($payment->batch_id) {
            PaymentRecord::where('batch_id', $payment->batch_id)
                ->where('id', '!=', $payment->id)
                ->delete();
        }

        // Update existing lead record with first month
        $payment->update(array_merge($baseData, [
            'payment_month' => $months[0] . '-01',
            'batch_id' => $batchId,
        ]));

        // Create records for remaining months
        $created = 0;
        $skipped = 0;
        foreach (array_slice($months, 1) as $monthStr) {
            $paymentMonth = $monthStr . '-01';
            $exists = PaymentRecord::where('resident_id', $payment->resident_id)
                ->where('payment_month', $paymentMonth)
                ->where('id', '!=', $payment->id)
                ->exists();
            if ($exists) {
                $skipped++;
                continue;
            }
            PaymentRecord::create(array_merge($baseData, [
                'resident_id' => $payment->resident_id,
                'payment_month' => $paymentMonth,
                'batch_id' => $batchId,
                'submitted_by' => $payment->submitted_by,
            ]));
            $created++;
        }

        $msg = 'Payment updated successfully.';
        if ($created)
            $msg .= " {$created} additional month(s) added.";
        if ($skipped)
            $msg .= " {$skipped} month(s) skipped (already exist).";

        return redirect()->route('payments.index')->with('success', $msg);
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
