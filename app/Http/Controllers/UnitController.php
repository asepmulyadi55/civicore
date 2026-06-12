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
            ->with('householder')
            ->orderByRaw("
                LEFT(unit_number, LOCATE('-', unit_number) - 1),
                CAST(SUBSTRING(unit_number, LOCATE('-', unit_number) + 1) AS UNSIGNED),
                unit_number
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
            ->with('success', __('app.flash_unit_added', ['unit' => $request->unit_number, 'block' => $block->name]));
    }

    /**
     * Update an existing unit.
     */
    public function update(UpdateUnitRequest $request, Block $block, Unit $unit)
    {
        $unit->update($request->validated());

        return redirect()
            ->route('blocks.units.index', $block)
            ->with('success', __('app.flash_unit_updated', ['unit' => $unit->unit_number]));
    }

    /**
     * Delete a unit — blocked if a householder is linked.
     */
    public function destroy(Block $block, Unit $unit)
    {
        abort_if(auth()->user()->cannot('blocks.edit'), 403);

        if ($unit->householder) {
            return redirect()
                ->route('blocks.units.index', $block)
                ->with('error_delete_unit', $unit->id);
        }

        $label = $unit->unit_number;
        $unit->delete();

        return redirect()
            ->route('blocks.units.index', $block)
            ->with('success', __('app.flash_unit_deleted', ['unit' => $label]));
    }

    /**
     * AJAX — return units for a block (for householder form dropdown).
     * Marks each unit whether it is already occupied.
     * Accepts optional ?current_unit_id=xxx to always include the current unit.
     */
    public function apiList(Request $request, Block $block)
    {
        $currentUnitId = $request->get('current_unit_id');

        $units = $block->units()
            ->active()
            ->with('householder:id,unit_id,fullname')
            ->orderBy('unit_number')
            ->get(['id', 'unit_number', 'house_status'])
            ->map(function (Unit $unit) use ($currentUnitId) {
                $isOccupied = $unit->householder !== null;
                $isCurrent  = $unit->id === $currentUnitId;

                return [
                    'id'                 => $unit->id,
                    'unit_number'        => $unit->unit_number,
                    'house_status'       => $unit->house_status,
                    'house_status_label' => __('app.house_status_' . $unit->house_status),
                    'is_occupied'        => $isOccupied && !$isCurrent,
                    'occupied_by'        => ($isOccupied && !$isCurrent) ? $unit->householder->fullname : null,
                ];
            });

        return response()->json($units);
    }

    /**
     * Bulk delete units.
     */
    public function bulkDestroy(Request $request, Block $block)
    {
        abort_if(auth()->user()->cannot('blocks.edit'), 403);

        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->route('blocks.units.index', $block)->with('error', __('app.no_items_selected'));
        }

        $deletedCount = 0;
        $skippedCount = 0;

        $units = $block->units()->whereIn('id', $ids)->get();

        foreach ($units as $unit) {
            if ($unit->householder) {
                $skippedCount++;
            } else {
                $unit->delete();
                $deletedCount++;
            }
        }

        $message = __('app.flash_units_bulk_deleted', ['count' => $deletedCount]);
        if ($skippedCount > 0) {
            $message .= ' ' . __('app.flash_units_bulk_skipped', ['count' => $skippedCount]);
        }

        return redirect()->route('blocks.units.index', $block)->with('success', $message);
    }
}
