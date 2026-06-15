<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Block;
use App\Models\MediaFile;
use App\Models\PaymentRecord;
use App\Models\Householder;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Unit;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $isCoordinator = $user->isBlockCoordinator();
        $scopeBlockIds = $isCoordinator ? $user->coordinatedBlockIds() : null;
        $canApprove = $user->can('payments.approve');

        // Build base query scoped to coordinator's blocks if applicable
        $baseQ = PaymentRecord::query()
            ->when($scopeBlockIds, fn($q) => $q->whereIn('block_id', $scopeBlockIds));

        // Apply search, block, status, and month filters
        if ($search = $request->get('search')) {
            $baseQ->where(function ($q) use ($search) {
                $q->where('householder_name', 'like', "%{$search}%")
                  ->orWhere('unit_number', 'like', "%{$search}%")
                  ->orWhereHas('householder', function ($hq) use ($search) {
                      $hq->where('fullname', 'like', "%{$search}%")
                         ->orWhereHas('unit', fn($u) => $u->where('unit_number', 'like', "%{$search}%"));
                  });
            });
        }
        if (!$scopeBlockIds && $blockId = $request->get('block_id')) {
            $baseQ->where('block_id', $blockId);
        }
        if ($status = $request->get('status')) {
            $baseQ->where('status', $status);
        }
        if ($month = $request->get('month')) {
            $baseQ->where('payment_month', 'like', $month . '%');
        }
        // Filter by the month/year the payment was RECORDED (created_at) — for validating finance reports
        if ($recordedMonth = $request->get('recorded_month')) {
            $baseQ->whereMonth('created_at', $recordedMonth);
        }
        if ($recordedYear = $request->get('recorded_year')) {
            $baseQ->whereYear('created_at', $recordedYear);
        }

        $payments = $this->buildBatchedPaginator($baseQ, $request);
        $stats = $this->buildStats($scopeBlockIds);
        $residentData = $this->buildResidentData($scopeBlockIds);

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
    private function buildResidentData(?array $scopeBlockIds): array
    {
        $residents = Householder::with(['block', 'feeHistories'])
            ->where('is_active', true)
            ->when($scopeBlockIds, fn($q) => $q->whereIn('block_id', $scopeBlockIds))
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
            ->with(['householder.block', 'paymentMethod', 'submittedBy'])
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
                ? $flatRows->sortBy(fn($p) => $p->householder?->fullname)->values()
                : $flatRows->sortByDesc(fn($p) => $p->householder?->fullname)->values();
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
    private function buildStats(?array $scopeBlockIds): array
    {
        $statBase = PaymentRecord::when(
            $scopeBlockIds,
            fn($q) => $q->whereIn('block_id', $scopeBlockIds)
        );

        $pendingCount = (clone $statBase)->where('status', 'pending')->count();
        $pendingTotal = (clone $statBase)->where('status', 'pending')->sum('amount');

        $periodStart = now()->startOfMonth()->startOfDay();
        $periodEnd   = now()->endOfMonth()->endOfDay();

        $collectedMonth = (clone $statBase)->where('status', 'approved')
            ->where(function ($q) use ($periodStart, $periodEnd) {
                // Payments FOR this month, approved by end of this month
                $q->where(function ($q2) use ($periodStart, $periodEnd) {
                    $q2->whereYear('payment_month', now()->year)
                       ->whereMonth('payment_month', now()->month)
                       ->where('updated_at', '<=', $periodEnd);
                })
                // Payments for past months, but approved IN this month (backdated)
                ->orWhere(function ($q2) use ($periodStart, $periodEnd) {
                    $q2->where('payment_month', '<', $periodStart)
                       ->where('updated_at', '>=', $periodStart)
                       ->where('updated_at', '<=', $periodEnd);
                });
            })
            ->sum('amount');

        $unpaidCount = Householder::where('is_active', true)
            ->when($scopeBlockIds, fn($q) => $q->whereIn('block_id', $scopeBlockIds))
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
            ->when($scopeBlockIds, fn($q) => $q->whereIn('block_id', $scopeBlockIds))
            ->whereNotNull('householder_id')
            ->get(['householder_id', 'payment_month'])
            ->groupBy('householder_id')
            ->map($mapMonths)
            ->toArray();

        $pendingMonthsByResident = PaymentRecord::where('status', 'pending')
            ->when($scopeBlockIds, fn($q) => $q->whereIn('block_id', $scopeBlockIds))
            ->whereNotNull('householder_id')
            ->get(['householder_id', 'payment_month'])
            ->groupBy('householder_id')
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
            'resident_id' => ['required', 'exists:householders,id'],
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

        $householder = Householder::find($request->resident_id);

        $baseData = [
            'householder_id' => $request->resident_id,
            'householder_name' => $householder->fullname ?? null,
            'block_id' => $householder->block_id ?? null,
            'unit_number' => $householder->unit_number ?? null,
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
            $exists = PaymentRecord::where('householder_id', $request->resident_id)
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

        $msg = __('app.flash_payment_created', ['count' => $created]);
        if ($skipped) {
            $msg .= ' ' . __('app.flash_payment_skipped', ['count' => $skipped]);
        }

        return redirect()->route('payments.index')->with('success', $msg);
    }

    public function importExcel(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('payments.index')
                ->with('error', __('app.flash_payment_admin_only') ?? 'Only administrators can import payments.');
        }

        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
            'year'       => ['required', 'integer', 'min:2020', 'max:2035'],
        ], [
            'excel_file.required' => 'Please choose an Excel file.',
            'excel_file.mimes'    => 'Only .xlsx and .xls files are accepted.',
        ]);

        $year = (int) $request->input('year');
        $spreadsheet = IOFactory::load($request->file('excel_file')->getRealPath());
        $sheet       = $spreadsheet->getSheet(0);
        $maxRow      = $sheet->getHighestRow();

        $stats = ['created' => 0, 'skipped' => 0];

        DB::transaction(function () use ($sheet, $maxRow, $year, &$stats) {
            $currentBlock = '';
            for ($row = 2; $row <= $maxRow; $row++) {
                $blockLetter = strtoupper(trim($sheet->getCell('A' . $row)->getCalculatedValue() ?? ''));
                if ($blockLetter !== '') {
                    $currentBlock = $blockLetter;
                } else {
                    $blockLetter = $currentBlock;
                }

                $unitNum   = trim($sheet->getCell('B' . $row)->getCalculatedValue() ?? '');
                $name      = trim($sheet->getCell('C' . $row)->getCalculatedValue() ?? '');
                $rawStatus = strtolower(trim(preg_replace('/\s+/', ' ', $sheet->getCell('D' . $row)->getCalculatedValue() ?? '')));

                // Skip empty rows, header repeats, common areas
                if (empty($blockLetter) || empty($unitNum) || empty($name)) continue;
                if (in_array($rawStatus, ['fasum', 'fasilitasumum', 'developer'])) continue;
                if (!ctype_alpha($blockLetter)) continue;

                // Find Block + Unit
                $block = Block::where('name', $blockLetter)->first();
                if (!$block) continue;

                $unit = Unit::where('block_id', $block->id)->where('unit_number', $unitNum)->first();
                if (!$unit) continue;

                // Find active householder
                $householder = Householder::where('unit_id', $unit->id)->where('is_active', true)->first();
                if (!$householder) continue;

                // Amount per month
                $amount = (float) ($sheet->getCell('E' . $row)->getCalculatedValue() ?? 0);
                if ($amount <= 0) continue;

                $batchId = (string) Str::uuid(); // Unique batch ID for this householder's imported payments

                // Loop through columns F (Jan) to Q (Dec)
                // Col 6 = F (Jan), Col 7 = G (Feb) ... Col 17 = Q (Dec)
                // In PhpSpreadsheet, getCellByColumnAndRow(1, 1) means A1.
                // Col 1=A, 2=B, 3=C, 4=D, 5=E, 6=F (Jan) ... 17=Q (Dec)
                for ($col = 6; $col <= 17; $col++) {
                    $monthNum = $col - 5; // 1 to 12
                    $cellValue = strtoupper(trim($sheet->getCell([$col, $row])->getCalculatedValue() ?? ''));
                    
                    if ($cellValue === 'L') {
                        $paymentMonth = Carbon::create($year, $monthNum, 1)->format('Y-m-d');
                        
                        $exists = PaymentRecord::where('householder_id', $householder->id)
                            ->where('payment_month', $paymentMonth)
                            ->exists();

                        if ($exists) {
                            $stats['skipped']++;
                            continue;
                        }

                        PaymentRecord::create([
                            'householder_id'   => $householder->id,
                            'householder_name' => $householder->fullname,
                            'block_id'         => $householder->block_id,
                            'unit_number'      => $householder->unit_number,
                            'payment_month'    => $paymentMonth,
                            'amount'           => $amount,
                            'status'           => 'approved',
                            'approved_by'      => auth()->id(),
                            'approved_at'      => now(),
                            'submitted_by'     => auth()->id(),
                            'notes'            => "Imported from Excel ({$year})",
                            'batch_id'         => $batchId,
                        ]);
                        $stats['created']++;
                    }
                }
            }
        });

        $msg = __('app.flash_payments_imported', [
            'count' => $stats['created'],
            'skipped' => $stats['skipped']
        ]);
        if (!trans()->has('app.flash_payments_imported')) {
            $msg = "Import complete! Created {$stats['created']} payments. Skipped {$stats['skipped']} (already exists).";
        }

        return redirect()->route('payments.index')->with('success', $msg);
    }


    public function update(Request $request, PaymentRecord $payment)
    {
        $request->validate([
            'months'            => ['required', 'array', 'min:1'],
            'months.*'          => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'amount'            => ['required', 'numeric', 'min:0'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'notes'             => ['nullable', 'string', 'max:1000'],
            'proof'             => ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/webp,application/pdf', 'max:5120'],
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

        // Editing a payment always resets it to pending so it gets re-reviewed.
        $householder = $payment->householder; // May be null if householder was deleted, but edit is rare then
        $baseData = [
            'householder_name'  => $householder ? $householder->fullname : $payment->householder_name,
            'block_id'          => $householder ? $householder->block_id : $payment->block_id,
            'unit_number'       => $householder ? $householder->unit_number : $payment->unit_number,
            'amount'            => $request->amount,
            'payment_method_id' => $request->payment_method_id ?: null,
            'status'            => 'pending',
            'rejection_reason'  => null,
            'notes'             => $request->notes,
            'proof_path'        => $proofPath,
            'approved_by'       => null,
            'approved_at'       => null,
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
            $exists = PaymentRecord::where('householder_id', $payment->householder_id)
                ->where('payment_month', $paymentMonth)
                ->where('id', '!=', $payment->id)
                ->exists();
            if ($exists) {
                $skipped++;
                continue;
            }
            PaymentRecord::create(array_merge($baseData, [
                'householder_id' => $payment->householder_id,
                'payment_month'  => $paymentMonth,
                'batch_id'       => $batchId,
                'submitted_by'   => $payment->submitted_by,
            ]));
            $created++;
        }

        $msg = __('app.flash_payment_updated');
        if ($created) {
            $msg .= ' ' . __('app.flash_payment_months_added', ['count' => $created]);
        }
        if ($skipped) {
            $msg .= ' ' . __('app.flash_payment_months_skipped', ['count' => $skipped]);
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

        $residentName = $payment->householder->fullname ?? 'Unknown';
        Log::info('Payment approved', [
            'payment_id' => $payment->id,
            'resident' => $residentName,
            'amount' => $payment->amount,
            'month' => $payment->payment_month,
            'approved_by' => auth()->id(),
        ]);
        return redirect()->route('payments.index')
            ->with('success', __('app.flash_payment_approved', ['name' => $residentName]));
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
            'resident' => $payment->householder->fullname ?? 'Unknown',
            'reason' => $request->rejection_reason,
            'rejected_by' => auth()->id(),
        ]);
        return redirect()->route('payments.index')
            ->with('success', __('app.flash_payment_rejected'));
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
                ->with('info', __('app.flash_payment_no_pending'));
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

        $name = $records->first()?->householder?->fullname ?? 'Resident';
        Log::info('Batch payments approved', [
            'batch_id' => $batchId,
            'count' => $count,
            'resident' => $name,
            'approved_by' => auth()->id(),
        ]);
        return redirect()->route('payments.index')
            ->with('success', __('app.flash_payments_approved', ['count' => $count, 'name' => $name]));
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

        $name = $records->first()?->householder?->fullname ?? 'Resident';
        Log::info('Batch payments rejected', [
            'batch_id' => $batchId,
            'count' => $count,
            'resident' => $name,
            'reason' => $request->rejection_reason,
            'rejected_by' => auth()->id(),
        ]);
        return redirect()->route('payments.index')
            ->with('success', __('app.flash_payments_rejected', ['count' => $count, 'name' => $name]));
    }

    /**
     * Validate whether a payment (or its batch) can be safely deleted.
     * Returns an error message string, or null if deletion is allowed.
     */
    private function canDeletePayment(PaymentRecord $payment): ?string
    {
        // Admin users can override and delete anything, even approved records
        if (auth()->user()->isAdmin()) {
            return null;
        }

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
                ->with('error', __('app.flash_payment_admin_only'));
        }

        // Validate deletion is safe (not approved, batch not partially approved)
        $errorMessage = $this->canDeletePayment($payment);
        if ($errorMessage !== null) {
            return redirect()->route('payments.index')->with('error', $errorMessage);
        }

        // Perform deletion
        $count = 1;
        $name = $payment->householder->fullname ?? 'Resident';
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
            ->with('success', __('app.flash_payments_deleted', ['count' => $count, 'name' => $name]));
    }

    public function bulkDestroy(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('payments.index')
                ->with('error', __('app.flash_payment_admin_only'));
        }

        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:payment_records,id',
        ]);

        $deletedCount = 0;
        $skippedCount = 0;

        $payments = PaymentRecord::whereIn('id', $request->input('ids', []))->get();
        $processedBatchIds = [];

        foreach ($payments as $payment) {
            if ($payment->batch_id) {
                if (in_array($payment->batch_id, $processedBatchIds)) {
                    continue;
                }
                
                $errorMessage = $this->canDeletePayment($payment);
                if ($errorMessage !== null) {
                    $skippedCount++; // Count as 1 UI item skipped
                    $processedBatchIds[] = $payment->batch_id;
                    continue;
                }

                PaymentRecord::where('batch_id', $payment->batch_id)->delete();
                $deletedCount++; // Count as 1 UI item deleted
                $processedBatchIds[] = $payment->batch_id;
            } else {
                $errorMessage = $this->canDeletePayment($payment);
                if ($errorMessage !== null) {
                    $skippedCount++;
                    continue;
                }
                $payment->delete();
                $deletedCount++;
            }
        }

        $msg = __('app.flash_payments_deleted', ['count' => $deletedCount, 'name' => 'records']);
        if ($skippedCount > 0) {
            $msg .= " ($skippedCount skipped because they contain approved records).";
        }

        return redirect()->route('payments.index')->with('success', $msg);
    }
}

