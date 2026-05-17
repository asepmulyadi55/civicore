<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Block;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * Show the unit management page for a block.
     */
    public function index(Block $block)
    {
        abort_if(auth()->user()->cannot('blocks.view'), 403);

        $units = $block->units()
            ->with('resident')
            ->orderByRaw("
                LEFT(unit_number, LOCATE('-', unit_number) - 1),
                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(unit_number, '-', -1), ' ', 1) AS UNSIGNED)
            ")
            ->get();


        $totalCount         = $units->count();
        $ownerOccupiedCount = $units->where('house_status', 'owner_occupied')->count();
        $rentedCount        = $units->where('house_status', 'rented')->count();
        $vacantCount        = $units->where('house_status', 'vacant')->count();

        return view('blocks.units', compact(
            'block', 'units', 'totalCount', 'ownerOccupiedCount', 'rentedCount', 'vacantCount'
        ));
    }

    /**
     * Store a new unit for this block.
     */
    public function store(StoreUnitRequest $request, Block $block)
    {
        $block->units()->create($request->validated());

        return redirect()
            ->route('blocks.units.index', $block)
            ->with('success', "Unit \"{$request->unit_number}\" added to {$block->name}.");
    }

    /**
     * Update an existing unit.
     */
    public function update(UpdateUnitRequest $request, Block $block, Unit $unit)
    {
        $unit->update($request->validated());

        return redirect()
            ->route('blocks.units.index', $block)
            ->with('success', "Unit \"{$unit->unit_number}\" updated.");
    }

    /**
     * Delete a unit — blocked if a resident is linked.
     */
    public function destroy(Block $block, Unit $unit)
    {
        abort_if(auth()->user()->cannot('blocks.edit'), 403);

        if ($unit->resident) {
            return redirect()
                ->route('blocks.units.index', $block)
                ->with('error_delete_unit', $unit->id);
        }

        $label = $unit->unit_number;
        $unit->delete();

        return redirect()
            ->route('blocks.units.index', $block)
            ->with('success', "Unit \"{$label}\" deleted.");
    }

    /**
     * AJAX — return units for a block (for resident form dropdown).
     * Marks each unit whether it is already occupied.
     * Accepts optional ?current_unit_id=xxx to always include the current unit.
     */
    public function apiList(Request $request, Block $block)
    {
        $currentUnitId = $request->get('current_unit_id');

        $units = $block->units()
            ->active()
            ->with('resident:id,unit_id,fullname')
            ->orderBy('unit_number')
            ->get(['id', 'unit_number', 'house_status'])
            ->map(function (Unit $unit) use ($currentUnitId) {
                $isOccupied = $unit->resident !== null;
                $isCurrent  = $unit->id === $currentUnitId;

                return [
                    'id'                 => $unit->id,
                    'unit_number'        => $unit->unit_number,
                    'house_status'       => $unit->house_status,
                    'house_status_label' => __('app.house_status_' . $unit->house_status),
                    'is_occupied'        => $isOccupied && !$isCurrent,
                    'occupied_by'        => ($isOccupied && !$isCurrent) ? $unit->resident->fullname : null,
                ];
            });

        return response()->json($units);
    }
}
