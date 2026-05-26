<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Block;
use App\Models\MediaFile;
use App\Models\PaymentRecord;
use App\Models\Resident;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $scopeBlockId = $user->isBlockCoordinator() ? $user->block_id : null;
        $canApprove = $user->can('payments.approve');

        // Build base query scoped to coordinator's block if applicable
        $baseQ = PaymentRecord::query()
            ->when($scopeBlockId, fn($q) => $q->whereHas('resident', fn($r) => $r->where('block_id', $scopeBlockId)));

        // Apply search, block, status, and month filters
        if ($search = $request->get('search')) {
            $baseQ->whereHas('resident', function ($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%")
                    ->orWhereHas('unit', fn($u) => $u->where('unit_number', 'like', "%{$search}%"));
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

        $payments = $this->buildBatchedPaginator($baseQ, $request);
        $stats = $this->buildStats($scopeBlockId);
        $residentData = $this->buildResidentData($scopeBlockId);

        $blocks = Block::active()->orderBy('name')->get();
        $currency = Setting::get('currency_symbol', 'Rp');
        // Admin and Treasurer can edit approved payments; Block Coordinator cannot
        $canEditApproved = $user->isAdmin() || $user->can('payments.approve');

        return view('payments', array_merge(
            compact('payments', 'blocks', 'currency', 'canApprove', 'canEditApproved'),
            $stats,
            $residentData
        ));
    }
    /**
     * Load residents list and their current fee map for the JS payment modal.
     * Returns ['residents' => Collection, 'residentFees' => Collection].
     */
    private function buildResidentData(?string $scopeBlockId): array
    {
        $residents = Resident::with(['block', 'feeHistories'])
            ->where('is_active', true)
            ->when($scopeBlockId, fn($q) => $q->where('block_id', $scopeBlockId))
            ->orderBy('fullname')
            ->get();

        // Build residentId → current fee amount map for the JS modal
        $residentFees = $residents->mapWithKeys(function ($r) {
            /** @var \App\Models\Resident $r */
            return [$r->id => (float) ($r->currentFee()?->amount ?? 0)];
        });

        return compact('residents', 'residentFees');
    }


    private function buildBatchedPaginator(
        \Illuminate\Database\Eloquent\Builder $baseQ,
        Request $request
    ): \Illuminate\Pagination\LengthAwarePaginator {
        $allRecords = $baseQ
            ->with(['resident.block', 'paymentMethod', 'submittedBy'])
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'rejected' THEN 1 ELSE 2 END ASC")
            ->orderByDesc('payment_month')
            ->get();

        // Collapse multi-month batches into a single lead record
        $flatRows = $allRecords
            ->groupBy(fn($p) => $p->batch_id ?? (string) $p->id)
            ->map(function ($records) {
                /** @var \App\Models\PaymentRecord $lead */
                $lead = $records->sortBy('payment_month')->first();
                $lead->all_months = $records->pluck('payment_month')->sort()->values();
                $lead->total_amount = $records->sum('amount');
                $lead->month_count = $records->count();
                $lead->all_ids = $records->pluck('id')->all();

                return $lead;
            })
            ->values();

        // Apply collection-level sort (after batch grouping)
        $sort = $request->get('sort');
        $dir  = $request->get('direction', 'desc');
        if ($sort === 'resident') {
            $flatRows = $dir === 'asc'
                ? $flatRows->sortBy(fn($p) => $p->resident?->fullname)->values()
                : $flatRows->sortByDesc(fn($p) => $p->resident?->fullname)->values();
        } elseif ($sort === 'amount') {
            $flatRows = $dir === 'asc'
                ? $flatRows->sortBy('total_amount')->values()
                : $flatRows->sortByDesc('total_amount')->values();
        } elseif ($sort === 'status') {
            $flatRows = $dir === 'asc'
                ? $flatRows->sortBy('status')->values()
                : $flatRows->sortByDesc('status')->values();
        } elseif ($sort === 'month') {
            $flatRows = $dir === 'asc'
                ? $flatRows->sortBy('payment_month')->values()
                : $flatRows->sortByDesc('payment_month')->values();
        }
        // else keep default: pending first, then by month desc (from the DB orderBy above)

        $perPage = config('civicore.pagination.payments', 20);
        $total   = $flatRows->count();

        // If filters changed and the current page no longer exists, fall back to 1
        $lastPage = (int) ceil($total / $perPage) ?: 1;
        $page     = max(1, min((int) $request->get('page', 1), $lastPage));

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $flatRows->slice(($page - 1) * $perPage, $perPage)->values(),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    /**
     * Build summary stats (counts / totals) for the payments dashboard header.
     * Returns array suitable for merging into view compact().
     */
    private function buildStats(?string $scopeBlockId): array
    {
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
                fn($q) => $q
                    ->where('status', 'approved')
                    ->whereYear('payment_month', now()->year)
                    ->whereMonth('payment_month', now()->month)
            )
            ->count();

        $mapMonths = fn($rows) => $rows
            ->map(fn($r) => Carbon::parse($r->payment_month)->format('Y-m'))
            ->values()->all();

        $paidMonthsByResident = PaymentRecord::where('status', 'approved')
            ->when($scopeBlockId, fn($q) => $q->whereHas('resident', fn($r) => $r->where('block_id', $scopeBlockId)))
            ->get(['resident_id', 'payment_month'])
            ->groupBy('resident_id')
            ->map($mapMonths)
            ->toArray();

        $pendingMonthsByResident = PaymentRecord::where('status', 'pending')
            ->when($scopeBlockId, fn($q) => $q->whereHas('resident', fn($r) => $r->where('block_id', $scopeBlockId)))
            ->get(['resident_id', 'payment_month'])
            ->groupBy('resident_id')
            ->map($mapMonths)
            ->toArray();

        return compact(
            'pendingCount',
            'pendingTotal',
            'collectedMonth',
            'unpaidCount',
            'paidMonthsByResident',
            'pendingMonthsByResident'
        );
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
            'proof' => ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/webp,application/pdf', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofFile = $request->file('proof');
            $proofPath = $proofFile->store('proofs', 'local');
            MediaFile::create([
                'disk'          => 'local',
                'path'          => $proofPath,
                'original_name' => $proofFile->getClientOriginalName(),
                'mime_type'     => $proofFile->getMimeType(),
                'size'          => $proofFile->getSize(),
                'uploaded_by'   => auth()->id(),
            ]);
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

        // Residents can only submit 'pending' — they cannot self-approve
        $user = auth()->user();
        $allowedStatuses = $user->can('payments.approve')
            ? ['unpaid', 'pending', 'approved']
            : ['pending'];
        if (!in_array($request->status, $allowedStatuses, true)) {
            return back()->withErrors(['status' => 'You are not authorized to set this payment status.'])->withInput();
        }

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
        if ($skipped) {
            $msg .= " {$skipped} skipped (already exist).";
        }

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
            'proof' => ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/webp,application/pdf', 'max:5120'],
        ]);

        // Handle proof replacement
        $proofPath = $payment->proof_path;
        if ($request->hasFile('proof')) {
            if ($proofPath) {
                \Storage::disk('local')->delete($proofPath);
                MediaFile::where('path', $proofPath)->delete();
            }
            $proofFile = $request->file('proof');
            $proofPath = $proofFile->store('proofs', 'local');
            MediaFile::create([
                'disk'          => 'local',
                'path'          => $proofPath,
                'original_name' => $proofFile->getClientOriginalName(),
                'mime_type'     => $proofFile->getMimeType(),
                'size'          => $proofFile->getSize(),
                'uploaded_by'   => auth()->id(),
            ]);
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
        if ($created) {
            $msg .= " {$created} additional month(s) added.";
        }
        if ($skipped) {
            $msg .= " {$skipped} month(s) skipped (already exist).";
        }

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

        $residentName = $payment->resident->fullname ?? 'Unknown';
        Log::info('Payment approved', [
            'payment_id' => $payment->id,
            'resident' => $residentName,
            'amount' => $payment->amount,
            'month' => $payment->payment_month,
            'approved_by' => auth()->id(),
        ]);
        return redirect()->route('payments.index')
            ->with('success', "Payment from {$residentName} has been approved.");
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

        Log::info('Payment rejected', [
            'payment_id' => $payment->id,
            'resident' => $payment->resident->fullname ?? 'Unknown',
            'reason' => $request->rejection_reason,
            'rejected_by' => auth()->id(),
        ]);
        return redirect()->route('payments.index')
            ->with('success', 'Payment rejected and resident has been notified.');
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

        // Bulk update in a single query — avoids N+1 and is safe without model observers
        PaymentRecord::where('batch_id', $batchId)
            ->where('status', 'pending')
            ->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

        $name = $records->first()?->resident?->fullname ?? 'Resident';
        Log::info('Batch payments approved', [
            'batch_id' => $batchId,
            'count' => $count,
            'resident' => $name,
            'approved_by' => auth()->id(),
        ]);
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

        // Bulk update — single query instead of N+1
        PaymentRecord::where('batch_id', $batchId)->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        $name = $records->first()?->resident?->fullname ?? 'Resident';
        Log::info('Batch payments rejected', [
            'batch_id' => $batchId,
            'count' => $count,
            'resident' => $name,
            'reason' => $request->rejection_reason,
            'rejected_by' => auth()->id(),
        ]);
        return redirect()->route('payments.index')
            ->with('success', "{$count} payment(s) from {$name} rejected.");
    }

    /**
     * Validate whether a payment (or its batch) can be safely deleted.
     * Returns an error message string, or null if deletion is allowed.
     */
    private function canDeletePayment(PaymentRecord $payment): ?string
    {
        if ($payment->isApproved()) {
            return 'Approved payments cannot be deleted. They are part of the financial record.';
        }

        if ($payment->batch_id) {
            $hasApproved = PaymentRecord::where('batch_id', $payment->batch_id)
                ->where('status', PaymentStatus::Approved)
                ->exists();
            if ($hasApproved) {
                return 'Cannot delete — the batch contains approved records.';
            }
        }

        return null;
    }

    public function destroy(PaymentRecord $payment)
    {
        // Only admin can delete payments (route middleware already restricts by permission;
        // this extra check enforces the admin-only business rule explicitly)
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('payments.index')
                ->with('error', 'Only administrators can delete payments.');
        }

        // Validate deletion is safe (not approved, batch not partially approved)
        $errorMessage = $this->canDeletePayment($payment);
        if ($errorMessage !== null) {
            return redirect()->route('payments.index')->with('error', $errorMessage);
        }

        // Perform deletion
        $count = 1;
        $name = $payment->resident->fullname ?? 'Resident';
        if ($payment->batch_id) {
            $count = PaymentRecord::where('batch_id', $payment->batch_id)->count();
            PaymentRecord::where('batch_id', $payment->batch_id)->delete();
        } else {
            $payment->delete();
        }

        Log::info('Payment deleted', [
            'batch_id' => $payment->batch_id,
            'count' => $count,
            'resident' => $name,
            'deleted_by' => auth()->id(),
        ]);

        return redirect()->route('payments.index')
            ->with('success', "{$count} payment record(s) for {$name} deleted.");
    }
}
