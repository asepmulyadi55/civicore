<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResidentRequest;
use App\Http\Requests\UpdateResidentRequest;
use App\Models\Block;
use App\Models\FeeHistory;
use App\Models\Resident;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ResidentController extends Controller
{
    public function index(Request $request)
    {
        $query = Resident::with([
            'block',
            'feeHistories' => function ($q) {
                $q->orderByDesc('effective_from')->limit(1);
            }
        ])->orderBy('block_id')->orderBy('unit_number');

        // Live search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%")
                    ->orWhere('unit_number', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('block', fn($b) => $b->where('name', 'like', "%{$search}%"));
            });
        }

        // Block filter
        if ($blockId = $request->get('block_id')) {
            $query->where('block_id', $blockId);
        }

        // Status filter
        if ($request->get('status') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->get('status') === 'inactive') {
            $query->where('is_active', false);
        }

        $residents = $query->paginate(15)->withQueryString();
        $blocks = Block::active()->orderBy('name')->get();
        $totalCount = Resident::count();
        $activeCount = Resident::where('is_active', true)->count();
        $currency = Setting::get('currency_symbol', 'Rp');

        return view('residents', compact('residents', 'blocks', 'totalCount', 'activeCount', 'currency'));
    }

    public function store(StoreResidentRequest $request)
    {
        DB::transaction(function () use ($request) {
            $resident = Resident::create([
                'block_id' => $request->block_id,
                'unit_number' => $request->unit_number,
                'fullname' => $request->fullname,
                'phone' => $request->phone,
                'is_active' => true,
            ]);

            // Create the initial fee history entry
            FeeHistory::create([
                'resident_id' => $resident->id,
                'amount' => $request->monthly_fee,
                'effective_from' => Carbon::createFromFormat('Y-m', $request->fee_start)->startOfMonth(),
                'created_by' => Auth::id(),
                'notes' => 'Initial fee assignment',
            ]);
        });

        return redirect()->route('residents.index')
            ->with('success', 'Resident added successfully.');
    }

    public function update(UpdateResidentRequest $request, Resident $resident)
    {
        $resident->update($request->only(['fullname', 'phone', 'block_id', 'unit_number', 'is_active']));

        return redirect()->route('residents.index')
            ->with('success', 'Resident updated successfully.');
    }

    public function destroy(Resident $resident)
    {
        // Soft-deactivate instead of hard delete to preserve payment history
        $resident->update(['is_active' => false]);

        return redirect()->route('residents.index')
            ->with('success', "{$resident->fullname} has been deactivated.");
    }
}
