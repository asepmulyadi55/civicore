<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreBlockRequest;
use App\Http\Resources\API\BlockResource;
use App\Models\Block;
use App\Models\Unit;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlockController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        if (!$request->user()->can('blocks.view')) {
            return $this->forbidden();
        }

        $query = Block::withCount(['residents', 'units']);

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $query->orderBy($request->input('sort_by', 'name'), $request->input('sort_order', 'asc') === 'desc' ? 'desc' : 'asc');

        $paginator = $query->paginate($request->input('per_page', 15));

        return $this->paginated($paginator, BlockResource::collection($paginator), 'Blocks fetched successfully');
    }

    public function show(Request $request, Block $block): JsonResponse
    {
        if (!$request->user()->can('blocks.view')) {
            return $this->forbidden();
        }

        $block->loadCount(['residents', 'units']);

        return $this->success(new BlockResource($block), 'Block fetched successfully');
    }

    public function store(StoreBlockRequest $request): JsonResponse
    {
        $block = Block::create($request->validated());
        $block->loadCount(['residents', 'units']);

        return $this->created(new BlockResource($block), 'Block created successfully');
    }

    public function update(Request $request, Block $block): JsonResponse
    {
        if (!$request->user()->can('blocks.edit')) {
            return $this->forbidden();
        }

        $data = $request->validate([
            'name'        => ['sometimes', 'string', 'max:100', "unique:blocks,name,{$block->id}"],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active'   => ['boolean'],
        ]);

        $block->update($data);
        $block->loadCount(['residents', 'units']);

        return $this->success(new BlockResource($block), 'Block updated successfully');
    }

    public function destroy(Request $request, Block $block): JsonResponse
    {
        if (!$request->user()->can('blocks.delete')) {
            return $this->forbidden();
        }

        if ($block->residents()->exists()) {
            return $this->error('Cannot delete block with assigned residents.', 422);
        }

        $block->delete();

        return $this->noContent('Block deleted successfully');
    }

    /**
     * AJAX — return available units for a block (used by web forms too).
     */
    public function units(Request $request, Block $block): JsonResponse
    {
        $currentUnitId = $request->input('current_unit_id');

        $units = $block->units()
            ->active()
            ->with('resident:id,unit_id,fullname')
            ->orderBy('unit_number')
            ->get(['id', 'unit_number', 'house_status'])
            ->map(function (Unit $unit) use ($currentUnitId) {
                $isOccupied = $unit->resident !== null;
                $isCurrent  = $unit->id === $currentUnitId;

                return [
                    'id'           => $unit->id,
                    'unit_number'  => $unit->unit_number,
                    'house_status' => $unit->house_status,
                    'is_occupied'  => $isOccupied && !$isCurrent,
                    'occupied_by'  => ($isOccupied && !$isCurrent) ? $unit->resident->fullname : null,
                ];
            });

        return response()->json($units);
    }
}

