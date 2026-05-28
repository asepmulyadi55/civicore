<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinanceReport;
use App\Models\PaymentRecord;
use App\Models\Resident;
use App\Enums\PaymentStatus;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use ApiResponse;

    /**
     * Finance summary: total collected, pending, and unpaid for a given month/year.
     */
    public function financeSummary(Request $request): JsonResponse
    {
        if (!$request->user()->can('reports.view')) {
            return $this->forbidden();
        }

        $year  = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $payments = PaymentRecord::whereYear('payment_month', $year)
            ->whereMonth('payment_month', $month)
            ->selectRaw('status, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $totalResidents = Resident::where('is_active', true)->count();

        return $this->success([
            'year'           => $year,
            'month'          => $month,
            'total_residents' => $totalResidents,
            'approved'       => [
                'count' => $payments[PaymentStatus::Approved->value]?->count ?? 0,
                'total' => (float) ($payments[PaymentStatus::Approved->value]?->total ?? 0),
            ],
            'pending'        => [
                'count' => $payments[PaymentStatus::Pending->value]?->count ?? 0,
                'total' => (float) ($payments[PaymentStatus::Pending->value]?->total ?? 0),
            ],
            'rejected'       => [
                'count' => $payments[PaymentStatus::Rejected->value]?->count ?? 0,
                'total' => (float) ($payments[PaymentStatus::Rejected->value]?->total ?? 0),
            ],
        ], 'Finance summary fetched successfully');
    }

    /**
     * Monthly payment breakdown per resident.
     */
    public function monthlyReport(Request $request): JsonResponse
    {
        if (!$request->user()->can('reports.view')) {
            return $this->forbidden();
        }

        $year  = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $payments = PaymentRecord::with(['resident.block', 'resident.unit', 'paymentMethod'])
            ->whereYear('payment_month', $year)
            ->whereMonth('payment_month', $month)
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 20));

        $data = $payments->map(fn($p) => [
            'id'             => $p->id,
            'resident'       => $p->resident?->fullname,
            'block'          => $p->resident?->block?->name,
            'unit'           => $p->resident?->unit?->unit_number,
            'amount'         => (float) $p->amount,
            'status'         => $p->status?->value,
            'status_label'   => $p->status?->label(),
            'payment_method' => $p->paymentMethod?->label,
            'approved_at'    => $p->approved_at?->toISOString(),
        ]);

        return $this->paginated($payments, $data, 'Monthly report fetched successfully');
    }

    /**
     * Finance reports (formal ledger reports).
     */
    public function financeReports(Request $request): JsonResponse
    {
        if (!$request->user()->can('finance.view')) {
            return $this->forbidden();
        }

        $query = FinanceReport::orderByDesc('year')->orderByDesc('month');

        if ($year = $request->input('year')) {
            $query->where('year', $year);
        }

        $paginator = $query->paginate($request->input('per_page', 12));

        $data = $paginator->map(fn($r) => [
            'id'              => $r->id,
            'year'            => $r->year,
            'month'           => $r->month,
            'opening_balance' => (float) $r->opening_balance,
            'total_income'    => (float) $r->total_income,
            'total_expense'   => (float) $r->total_expense,
            'closing_balance' => (float) $r->closing_balance,
            'status'          => $r->status,
            'submitted_at'    => $r->submitted_at?->toISOString(),
            'approved_at'     => $r->approved_at?->toISOString(),
        ]);

        return $this->paginated($paginator, $data, 'Finance reports fetched successfully');
    }
}
