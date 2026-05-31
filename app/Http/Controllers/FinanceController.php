<?php

namespace App\Http\Controllers;

use App\Exports\FinanceReportExport;
use App\Models\FinanceReport;
use App\Models\FinanceTransaction;
use App\Models\PaymentRecord;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class FinanceController extends Controller
{
    // ── Main page (all tabs) ─────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $user      = auth()->user();
        $canManage = $user->can('finance.create');
        $canApprove = $user->can('finance.approve');

        // Residents can only see the Monthly Reports tab
        $defaultTab = $canManage ? 'dashboard' : 'reports';
        $tab        = $request->get('tab', $defaultTab);
        if (!$canManage && in_array($tab, ['dashboard', 'transactions'])) {
            $tab = 'reports';
        }
        $currency  = Setting::get('currency_symbol', 'Rp');

        $now          = now();
        $currentMonth = (int) $now->month;
        $currentYear  = (int) $now->year;

        // Allow viewing a past month's dashboard
        $selectedMonth = (int) $request->get('dash_month', $currentMonth);
        $selectedYear  = (int) $request->get('dash_year', $currentYear);
        if ($selectedMonth < 1 || $selectedMonth > 12) $selectedMonth = $currentMonth;
        if ($selectedYear < 2020 || $selectedYear > 2099) $selectedYear = $currentYear;

        // ── Dashboard aggregates ─────────────────────────────────────────────
        $dashData = $this->buildDashboardData($selectedMonth, $selectedYear);

        // ── Transactions data ────────────────────────────────────────────────
        $txData = $this->buildTransactionData($request);

        // ── Reports data ─────────────────────────────────────────────────────
        $rptData = $this->buildReportData($request);

        // ── Categories for datalist ──────────────────────────────────────────
        $categories = FinanceTransaction::distinctCategories();

        return view('finance', array_merge(
            compact('tab', 'currency', 'canManage', 'canApprove', 'currentMonth', 'currentYear', 'selectedMonth', 'selectedYear', 'categories'),
            $dashData,
            $txData,
            $rptData
        ));
    }

    // ── Store a manual transaction ───────────────────────────────────────────

    public function storeTransaction(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->can('finance.create')) abort(403);

        $validated = $request->validate([
            'type'             => ['required', 'in:income,expense'],
            'category'         => ['nullable', 'string', 'max:100'],
            'amount'           => ['required', 'numeric', 'min:0.01', 'max:999999999999.99'],
            'description'      => ['required', 'string', 'max:255'],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'transaction_date' => ['required', 'date'],
        ]);

        $date        = Carbon::parse($validated['transaction_date']);
        $reportMonth = (int) $date->month;
        $reportYear  = (int) $date->year;

        // Block edits on approved months (unless the user can approve)
        if ($this->isMonthLocked($reportMonth, $reportYear) && !$user->can('finance.approve')) {
            return back()
                ->withErrors(['general' => __('app.fin_report_locked')])
                ->withInput();
        }

        $transaction = FinanceTransaction::create([
            'type'             => $validated['type'],
            'category'         => $validated['category'] ?? null,
            'amount'           => $validated['amount'],
            'description'      => $validated['description'],
            'notes'            => $validated['notes'] ?? null,
            'transaction_date' => $date,
            'report_month'     => $reportMonth,
            'report_year'      => $reportYear,
            'created_by'       => $user->id,
        ]);

        $this->touchReport($reportMonth, $reportYear, $user->id);

        Log::info('Finance transaction created', ['id' => $transaction->id, 'by' => $user->id]);

        return redirect()
            ->route('finance.index', ['tab' => 'transactions'])
            ->with('success', __('app.fin_transaction_created'));
    }

    // ── Update a manual transaction ──────────────────────────────────────────

    public function updateTransaction(Request $request, FinanceTransaction $transaction): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->can('finance.edit')) abort(403);

        if ($this->isMonthLocked($transaction->report_month, $transaction->report_year)
            && !$user->can('finance.approve')) {
            return back()->withErrors(['general' => __('app.fin_report_locked')])->withInput();
        }

        $validated = $request->validate([
            'type'             => ['required', 'in:income,expense'],
            'category'         => ['nullable', 'string', 'max:100'],
            'amount'           => ['required', 'numeric', 'min:0.01', 'max:999999999999.99'],
            'description'      => ['required', 'string', 'max:255'],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'transaction_date' => ['required', 'date'],
        ]);

        $date        = Carbon::parse($validated['transaction_date']);
        $newMonth    = (int) $date->month;
        $newYear     = (int) $date->year;

        // If moving to a different month, check lock on that month too
        if (($newMonth !== $transaction->report_month || $newYear !== $transaction->report_year)
            && $this->isMonthLocked($newMonth, $newYear)
            && !$user->can('finance.approve')) {
            return back()->withErrors(['general' => __('app.fin_report_locked')])->withInput();
        }

        $oldMonth = $transaction->report_month;
        $oldYear  = $transaction->report_year;

        $transaction->update([
            'type'             => $validated['type'],
            'category'         => $validated['category'] ?? null,
            'amount'           => $validated['amount'],
            'description'      => $validated['description'],
            'notes'            => $validated['notes'] ?? null,
            'transaction_date' => $date,
            'report_month'     => $newMonth,
            'report_year'      => $newYear,
            'updated_by'       => $user->id,
        ]);

        $this->touchReport($newMonth, $newYear, $user->id);
        if ($oldMonth !== $newMonth || $oldYear !== $newYear) {
            $this->touchReport($oldMonth, $oldYear, $user->id);
        }

        Log::info('Finance transaction updated', ['id' => $transaction->id, 'by' => $user->id]);

        return redirect()
            ->route('finance.index', ['tab' => 'transactions'])
            ->with('success', __('app.fin_transaction_updated'));
    }

    // ── Delete a transaction ─────────────────────────────────────────────────

    public function destroyTransaction(FinanceTransaction $transaction): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->can('finance.delete')) abort(403);

        if ($this->isMonthLocked($transaction->report_month, $transaction->report_year)
            && !$user->can('finance.approve')) {
            return back()->withErrors(['general' => __('app.fin_report_locked')]);
        }

        $month = $transaction->report_month;
        $year  = $transaction->report_year;

        $transaction->delete();
        $this->touchReport($month, $year, $user->id);

        Log::info('Finance transaction deleted', ['by' => $user->id]);

        return redirect()
            ->route('finance.index', ['tab' => 'transactions'])
            ->with('success', __('app.fin_transaction_deleted'));
    }

    // ── Generate / refresh a monthly report ─────────────────────────────────

    public function generateReport(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->can('finance.create')) abort(403);

        $request->validate([
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year'  => ['required', 'integer', 'min:2020', 'max:2099'],
        ]);

        $month = (int) $request->month;
        $year  = (int) $request->year;

        // Don't regenerate if already approved (unless admin/approver)
        $existing = FinanceReport::where('month', $month)->where('year', $year)->first();
        if ($existing && $existing->status === 'approved' && !$user->can('finance.approve')) {
            return redirect()
                ->route('finance.index', ['tab' => 'reports'])
                ->withErrors(['general' => __('app.fin_report_locked')]);
        }

        DB::transaction(function () use ($month, $year, $user, $existing) {
            if (!$existing) {
                // Opening balance = closing balance of previous approved month, or 0
                $prevClosing = $this->getPreviousClosingBalance($month, $year);

                $existing = FinanceReport::create([
                    'month'           => $month,
                    'year'            => $year,
                    'opening_balance' => $prevClosing,
                    'status'          => 'draft',
                    'created_by'      => $user->id,
                ]);
            }

            $existing->recalculate();
            $existing->updated_by = $user->id;
            $existing->save();
        });

        Log::info('Finance report generated', ['month' => $month, 'year' => $year, 'by' => $user->id]);

        return redirect()
            ->route('finance.index', ['tab' => 'reports'])
            ->with('success', __('app.fin_report_generated'));
    }

    // ── Update opening balance ────────────────────────────────────────────────

    public function updateOpeningBalance(Request $request, FinanceReport $report): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->can('finance.edit')) abort(403);

        if ($report->status === 'approved' && !$user->can('finance.approve')) {
            return back()->withErrors(['general' => __('app.fin_report_locked')]);
        }

        $request->validate([
            'opening_balance' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
        ]);

        $report->opening_balance = $request->opening_balance;
        $report->recalculate();
        $report->updated_by = $user->id;
        $report->save();

        return back()->with('success', __('app.fin_report_updated'));
    }

    // ── Submit a report for approval ──────────────────────────────────────────

    public function submitReport(FinanceReport $report): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->can('finance.create')) abort(403);

        if (!in_array($report->status, ['draft', 'revised'])) {
            return back()->withErrors(['general' => 'Report cannot be submitted in its current state.']);
        }

        $report->recalculate();
        $report->status       = 'submitted';
        $report->submitted_by = $user->id;
        $report->submitted_at = now();
        $report->updated_by   = $user->id;
        $report->save();

        Log::info('Finance report submitted', ['id' => $report->id, 'by' => $user->id]);

        return redirect()
            ->route('finance.index', ['tab' => 'reports'])
            ->with('success', __('app.fin_report_submitted'));
    }

    // ── Approve a report ──────────────────────────────────────────────────────

    public function approveReport(FinanceReport $report): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->can('finance.approve')) abort(403);

        if ($report->status !== 'submitted') {
            return back()->withErrors(['general' => 'Report must be in submitted state to approve.']);
        }

        $report->status      = 'approved';
        $report->approved_by = $user->id;
        $report->approved_at = now();
        $report->updated_by  = $user->id;
        $report->save();

        Log::info('Finance report approved', ['id' => $report->id, 'by' => $user->id]);

        return redirect()
            ->route('finance.index', ['tab' => 'reports'])
            ->with('success', __('app.fin_report_approved'));
    }

    // ── Revise (unlock) an approved report ───────────────────────────────────

    public function reviseReport(Request $request, FinanceReport $report): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->can('finance.approve')) abort(403);

        if ($report->status !== 'approved') {
            return back()->withErrors(['general' => 'Only approved reports can be revised.']);
        }

        $request->validate(['notes' => ['nullable', 'string', 'max:1000']]);

        $report->status     = 'revised';
        $report->revised_by = $user->id;
        $report->revised_at = now();
        $report->updated_by = $user->id;
        if ($request->filled('notes')) {
            $report->notes = $request->notes;
        }
        $report->save();

        Log::info('Finance report marked for revision', ['id' => $report->id, 'by' => $user->id]);

        return redirect()
            ->route('finance.index', ['tab' => 'reports'])
            ->with('success', __('app.fin_report_revised'));
    }

    // ── Export a report as Excel ─────────────────────────────────────────────

    public function exportReport(FinanceReport $report)
    {
        $user = auth()->user();
        if (!$user->can('finance.view')) abort(403);

        $filename = 'finance-report-' . $report->year . '-' . str_pad($report->month, 2, '0', STR_PAD_LEFT) . '.xlsx';

        return Excel::download(new FinanceReportExport($report), $filename);
    }

    // ── Delete a draft/revised report ────────────────────────────────────────

    public function destroyReport(FinanceReport $report): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->can('finance.delete')) abort(403);

        if (!in_array($report->status, ['draft', 'revised'])) {
            return back()->withErrors(['general' => 'Only draft or revised reports can be deleted.']);
        }

        $report->delete();

        Log::info('Finance report deleted', ['id' => $report->id, 'by' => $user->id]);

        return redirect()
            ->route('finance.index', ['tab' => 'reports'])
            ->with('success', __('app.flash_report_deleted'));
    }

    // ── Category autocomplete ─────────────────────────────────────────────────

    public function searchCategories(Request $request)
    {
        if (!auth()->user()->can('finance.create')) abort(403);

        $q = $request->get('q', '');
        $categories = FinanceTransaction::whereNotNull('category')
            ->when($q, fn($query) => $query->where('category', 'like', "%{$q}%"))
            ->distinct()
            ->orderBy('category')
            ->limit(20)
            ->pluck('category');

        return response()->json($categories);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function buildDashboardData(int $month, int $year): array
    {
        // Current balance: closing balance of last approved report
        $latestApproved = FinanceReport::where('status', 'approved')
            ->orderByDesc('year')->orderByDesc('month')->first();
        $currentBalance = (float) ($latestApproved?->closing_balance ?? 0);

        // Current month income/expense
        $manualIncome = (float) FinanceTransaction::where('type', 'income')
            ->where('report_month', $month)->where('report_year', $year)->sum('amount');

        $paymentIncome = (float) PaymentRecord::where('status', 'approved')
            ->whereYear('payment_month', $year)
            ->whereMonth('payment_month', $month)
            ->sum('amount');

        $monthIncome = $manualIncome + $paymentIncome;
        $monthExpense = (float) FinanceTransaction::where('type', 'expense')
            ->where('report_month', $month)->where('report_year', $year)->sum('amount');

        $pendingPaymentsCount = PaymentRecord::where('status', 'pending')->count();
        $pendingPaymentsTotal = (float) PaymentRecord::where('status', 'pending')->sum('amount');

        // Monthly trend (last 6 months) — 2 queries
        $sixMonthsAgo = Carbon::now()->startOfMonth()->subMonths(5);

        $txTrend = FinanceTransaction::where(
            DB::raw("CONCAT(report_year, '-', LPAD(report_month, 2, '0'))"),
            '>=',
            $sixMonthsAgo->format('Y-m')
        )->select('type', 'report_month', 'report_year', DB::raw('SUM(amount) as total'))
            ->groupBy('type', 'report_month', 'report_year')
            ->get()
            ->groupBy(fn($r) => $r->report_year . '-' . str_pad($r->report_month, 2, '0', STR_PAD_LEFT));

        $trend    = [];
        $maxTrend = 1; // avoid division by zero
        for ($i = 5; $i >= 0; $i--) {
            $d   = Carbon::now()->startOfMonth()->subMonths($i);
            $key = $d->format('Y-m');

            $inc = (float) ($txTrend->get($key, collect())->where('type', 'income')->sum('total'));
            $exp = (float) ($txTrend->get($key, collect())->where('type', 'expense')->sum('total'));

            $trend[]  = ['label' => $d->format('M'), 'month' => (int)$d->month, 'year' => (int)$d->year, 'income' => $inc, 'expense' => $exp];
            $maxTrend = max($maxTrend, $inc, $exp);
        }

        $recentTransactions = FinanceTransaction::with('createdBy')
            ->orderByDesc('transaction_date')->orderByDesc('created_at')->limit(8)->get();

        $pendingReports = FinanceReport::where('status', 'submitted')
            ->orderByDesc('year')->orderByDesc('month')->get();

        return compact(
            'currentBalance', 'monthIncome', 'monthExpense',
            'pendingPaymentsCount', 'pendingPaymentsTotal',
            'trend', 'maxTrend', 'recentTransactions', 'pendingReports'
        );
    }

    private function buildTransactionData(Request $request): array
    {
        $q = FinanceTransaction::with('createdBy');

        if ($type = $request->get('tx_type')) {
            $q->where('type', $type);
        }
        if ($cat = $request->get('tx_category')) {
            $q->where('category', 'like', "%{$cat}%");
        }
        if ($ym = $request->get('tx_month')) {
            [$fy, $fm] = explode('-', $ym . '-1');
            $q->where('report_year', (int)$fy)->where('report_month', (int)$fm);
        }

        $transactions = $q->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return compact('transactions');
    }

    private function buildReportData(Request $request): array
    {
        $q = FinanceReport::with(['submittedBy', 'approvedBy']);

        // Residents only see approved reports
        if (!auth()->user()->can('finance.create')) {
            $q->where('status', 'approved');
        }

        if ($ry = $request->get('rpt_year')) {
            $q->where('year', (int)$ry);
        }

        $reports = $q->orderByDesc('year')->orderByDesc('month')
            ->paginate(12)
            ->withQueryString();

        return compact('reports');
    }

    private function isMonthLocked(int $month, int $year): bool
    {
        return FinanceReport::where('month', $month)
            ->where('year', $year)
            ->where('status', 'approved')
            ->exists();
    }

    /**
     * Get or create a report for the month, then recalculate its totals.
     */
    private function touchReport(int $month, int $year, string $userId): void
    {
        $report = FinanceReport::firstOrCreate(
            ['month' => $month, 'year' => $year],
            ['opening_balance' => $this->getPreviousClosingBalance($month, $year), 'status' => 'draft', 'created_by' => $userId]
        );

        // Don't auto-recalculate approved reports to preserve the snapshot
        if ($report->status !== 'approved') {
            $report->recalculate();
            $report->updated_by = $userId;
            $report->save();
        }
    }

    private function getPreviousClosingBalance(int $month, int $year): float
    {
        // Previous calendar month
        $prev = Carbon::create($year, $month, 1)->subMonth();

        $prevReport = FinanceReport::where('year', (int)$prev->year)
            ->where('month', (int)$prev->month)
            ->first();

        return $prevReport ? (float) $prevReport->closing_balance : 0.0;
    }
}
