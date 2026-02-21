<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\PaymentRecord;
use App\Models\Resident;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) $request->get('year', now()->year);
        $blockId = $request->get('block_id');
        $search = $request->get('search');
        $currency = Setting::get('currency_symbol', 'Rp');

        // Available years (seed data starts from 2023)
        $years = collect(range(now()->year, 2023));

        $blocks = Block::active()->orderBy('name')->get();

        // Build resident query
        $residentQuery = Resident::with([
            'block',
            'paymentRecords' => fn($q) => $q->whereYear('payment_month', $year)->orderBy('payment_month'),
        ])->where('is_active', true)
            ->orderBy('block_id')
            ->orderBy('unit_number');

        if ($blockId) {
            $residentQuery->where('block_id', $blockId);
        }
        if ($search) {
            $residentQuery->where(function ($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%")
                    ->orWhere('unit_number', 'like', "%{$search}%");
            });
        }

        $residents = $residentQuery->paginate(25)->withQueryString();

        // Summary stats for the selected year + block
        $baseQuery = PaymentRecord::whereYear('payment_month', $year);
        if ($blockId) {
            $baseQuery->whereHas('resident', fn($q) => $q->where('block_id', $blockId));
        }

        $totalResidents = $residentQuery->toBase()->count();
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
            'totalResidents'
        ));
    }
}
