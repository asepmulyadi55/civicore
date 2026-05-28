<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreResidentRequest;
use App\Http\Requests\API\UpdateResidentRequest;
use App\Http\Resources\API\ResidentResource;
use App\Models\Resident;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResidentController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        if (!$request->user()->can('residents.view')) {
            return $this->forbidden();
        }

        $query = Resident::with(['block', 'unit'])->withCount('familyMembers');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($blockId = $request->input('block_id')) {
            $query->where('block_id', $blockId);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $sortBy    = in_array($request->input('sort_by'), ['fullname', 'created_at', 'is_active']) ? $request->input('sort_by') : 'fullname';
        $sortOrder = $request->input('sort_order', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $sortOrder);

        $paginator = $query->paginate($request->input('per_page', 15));

        return $this->paginated($paginator, ResidentResource::collection($paginator), 'Residents fetched successfully');
    }

    public function show(Request $request, Resident $resident): JsonResponse
    {
        if (!$request->user()->can('residents.view')) {
            return $this->forbidden();
        }

        $resident->load(['block', 'unit', 'familyMembers']);

        return $this->success(new ResidentResource($resident), 'Resident fetched successfully');
    }

    public function store(StoreResidentRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $this->storePhoto($request);
            unset($data['photo']);
        }

        $resident = Resident::create($data);
        $resident->load(['block', 'unit']);

        return $this->created(new ResidentResource($resident), 'Resident created successfully');
    }

    public function update(UpdateResidentRequest $request, Resident $resident): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            if ($resident->photo_path) {
                Storage::disk('local')->delete($resident->photo_path);
            }
            $data['photo_path'] = $this->storePhoto($request);
            unset($data['photo']);
        }

        $resident->update($data);
        $resident->load(['block', 'unit']);

        return $this->success(new ResidentResource($resident), 'Resident updated successfully');
    }

    public function destroy(Request $request, Resident $resident): JsonResponse
    {
        if (!$request->user()->can('residents.delete')) {
            return $this->forbidden();
        }

        if ($resident->photo_path) {
            Storage::disk('local')->delete($resident->photo_path);
        }

        $resident->delete();

        return $this->noContent('Resident deleted successfully');
    }

    /**
     * Serve resident photo via authenticated route.
     */
    public function photo(Request $request, Resident $resident): mixed
    {
        if (!$request->user()->can('residents.view')) {
            return $this->forbidden();
        }

        if (!$resident->photo_path || !Storage::disk('local')->exists($resident->photo_path)) {
            return $this->notFound('Photo not found');
        }

        return Storage::disk('local')->response($resident->photo_path);
    }

    /**
     * AJAX — check if a resident exists by email.
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $resident = Resident::where('email', $request->email)->with('block')->first();

        if (!$resident) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found'       => true,
            'block_id'    => $resident->block_id,
            'block_name'  => $resident->block?->name ?? '—',
            'unit_number' => $resident->unit_number,
        ]);
    }

    private function storePhoto(Request $request): string
    {
        $file = $request->file('photo');
        $name = Str::uuid() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs('residents/photos', $name, 'local');
    }
}

