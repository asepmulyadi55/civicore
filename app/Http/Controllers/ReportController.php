<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Models\Block;
use App\Models\PaymentRecord;
use App\Models\Householder;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isCoordinator = $user->isBlockCoordinator();

        $year = (int) $request->get('year', now()->year);
        // Force coordinator to their own blocks; ignore any request block_id
        $scopeBlockIds = $isCoordinator ? $user->coordinatedBlockIds() : null;
        $blockId = $isCoordinator ? null : $request->get('block_id');
        $search = $request->get('search');
        $currency = Setting::get('currency_symbol', 'Rp');

        // Available years (seed data starts from 2023)
        $years = collect(range(now()->year, 2023));

        $blocks = Block::active()->orderBy('name')->get();

        // Build resident query
        $residentQuery = Householder::with([
            'block',
            'unit',
            'paymentRecords' => fn($q) => $q->whereYear('payment_month', $year)->orderBy('payment_month'),
        ])->where('householders.is_active', true)
            ->leftJoin('units', 'units.id', '=', 'householders.unit_id')
            ->orderBy('householders.block_id')
            ->orderByRaw('CAST(units.unit_number AS UNSIGNED)')
            ->select('householders.*');

        if ($scopeBlockIds) {
            $residentQuery->whereIn('householders.block_id', $scopeBlockIds);
        } elseif ($blockId) {
            $residentQuery->where('householders.block_id', $blockId);
        }
        if ($search) {
            $residentQuery->where(function ($q) use ($search) {
                $q->where('householders.fullname', 'like', "%{$search}%")
                    ->orWhere('units.unit_number', 'like', "%{$search}%");
            });
        }

        $residents = $residentQuery->paginate(25)->withQueryString();
        $totalResidents = $residents->total();

        // Summary stats for the selected year + block(s)
        $baseQuery = PaymentRecord::whereYear('payment_month', $year);
        if ($scopeBlockIds) {
            $baseQuery->whereHas('householder', fn($q) => $q->whereIn('block_id', $scopeBlockIds));
        } elseif ($blockId) {
            $baseQuery->whereHas('householder', fn($q) => $q->where('block_id', $blockId));
        }


        $paidCount = (clone $baseQuery)->where('status', 'approved')->count();
        $unpaidCount = (clone $baseQuery)->where('status', '!=', 'approved')->count();

        $collectionRate = ($paidCount + $unpaidCount) > 0
            ? round($paidCount / ($paidCount + $unpaidCount) * 100)
            : 0;

        $months = collect(range(1, 12))->map(fn($m) => [
            'num' => $m,
            'label' => Carbon::create($year, $m, 1)->format('M'),
        ]);

        return view('reports', compact(
            'residents',
            'blocks',
            'years',
            'months',
            'year',
            'blockId',
            'search',
            'currency',
            'paidCount',
            'unpaidCount',
            'collectionRate',
            'totalResidents',
            'isCoordinator'
        ));
    }

    /** Download the current view as an Excel file. */
    public function export(Request $request)
    {
        $user = auth()->user();
        $year = (int) $request->get('year', now()->year);
        $blockIds = $user->isBlockCoordinator()
            ? $user->coordinatedBlockIds()
            : ($request->get('block_id') ? [$request->get('block_id')] : null);

        $filename = 'report-' . $year . ($blockIds ? '-blocks' : '') . '.xlsx';

        return Excel::download(new ReportExport($year, $blockIds), $filename);
    }
}

