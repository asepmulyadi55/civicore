<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Unit;

class BlockController extends Controller
{
    /**
     * AJAX — return available units for a block.
     *
     * GET /api/blocks/{block}/units
     */
    public function units(Block $block)
    {
        $currentUnitId = request('current_unit_id');

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
