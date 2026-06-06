<?php

namespace App\Http\Controllers;

use App\Models\Householder;
use App\Models\OrganizationPeriod;
use App\Models\OrganizationPosition;
use App\Models\Resident;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    // ── Public view ───────────────────────────────────────────────────────────

    /**
     * GET /organization[?period_id=xxx]
     * All authenticated users may view.
     */
    public function index(Request $request): View
    {
        $periods = OrganizationPeriod::orderByDesc('start_year')->orderByDesc('end_year')->get();
        $activePeriod = $periods->firstWhere('is_active', true);

        $selectedId = $request->get('period_id', $activePeriod?->id);
        $selectedPeriod = $periods->firstWhere('id', $selectedId) ?? $activePeriod;

        $positions = collect();
        $tree      = [];

        if ($selectedPeriod) {
            $positions = OrganizationPosition::where('organization_period_id', $selectedPeriod->id)
                ->with([
                    'householder',
                    'householder.block',
                    'householder.unit',
                    'resident',
                    'resident.householder',
                    'resident.householder.block',
                    'resident.householder.unit',
                ])
                ->orderBy('sort_order')
                ->orderBy('created_at')
                ->get();

            $tree = $this->buildTree($positions);
        }

        return view('organization', compact('periods', 'selectedPeriod', 'positions', 'tree'));
    }

    // ── Period management ─────────────────────────────────────────────────────

    /**
     * POST /organization/periods
     */
    public function storePeriod(Request $request): RedirectResponse
    {
        $this->authorizeAction('organization.create');

        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'start_year' => 'required|integer|min:2000|max:2100',
            'end_year'   => 'required|integer|min:2000|max:2100|gte:start_year',
        ]);

        OrganizationPeriod::create($data);

        return back()->with('success', __('app.org_period_created'));
    }

    /**
     * PUT /organization/periods/{period}
     */
    public function updatePeriod(Request $request, OrganizationPeriod $period): RedirectResponse
    {
        $this->authorizeAction('organization.edit');

        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'start_year' => 'required|integer|min:2000|max:2100',
            'end_year'   => 'required|integer|min:2000|max:2100|gte:start_year',
        ]);

        $period->update($data);

        return back()->with('success', __('app.org_period_updated'));
    }

    /**
     * PATCH /organization/periods/{period}/activate
     * Sets this period as the single active period.
     */
    public function activatePeriod(OrganizationPeriod $period): RedirectResponse
    {
        $this->authorizeAction('organization.edit');

        DB::transaction(function () use ($period) {
            OrganizationPeriod::where('id', '!=', $period->id)->update(['is_active' => false]);
            $period->update(['is_active' => true]);
        });

        return back()->with('success', __('app.org_period_activated', ['name' => $period->name]));
    }

    /**
     * DELETE /organization/periods/{period}
     */
    public function destroyPeriod(OrganizationPeriod $period): RedirectResponse
    {
        $this->authorizeAction('organization.delete');

        if ($period->is_active) {
            return back()->with('error', __('app.org_period_delete_active_error'));
        }

        $period->delete();

        return back()->with('success', __('app.org_period_deleted'));
    }

    // ── Position management ───────────────────────────────────────────────────

    /**
     * POST /organization/periods/{period}/positions
     */
    public function storePosition(Request $request, OrganizationPeriod $period): RedirectResponse
    {
        $this->authorizeAction('organization.create');

        $data = $request->validate([
            'position_name'    => 'required|string|max:100',
            'parent_id'        => 'nullable|string|max:36',
            'resident_id'      => 'nullable|string|max:36',
            'householder_id'   => 'nullable|string|max:36',
            'sort_order'       => 'nullable|integer|min:0',
        ]);

        if (!empty($data['parent_id'])) {
            if (!OrganizationPosition::where('id', $data['parent_id'])
                    ->where('organization_period_id', $period->id)->exists()) {
                return back()->withErrors(['parent_id' => __('app.org_invalid_parent')])->withInput();
            }
        }

        $error = $this->checkPersonConflict($period->id, $data['householder_id'] ?? null, $data['resident_id'] ?? null);
        if ($error) return back()->withErrors($error)->withInput();

        OrganizationPosition::create([
            'organization_period_id' => $period->id,
            'parent_id'              => $data['parent_id'] ?? null,
            'householder_id'         => $data['householder_id'] ?? null,
            'resident_id'            => $data['resident_id'] ?? null,
            'position_name'          => $data['position_name'],
            'sort_order'             => $data['sort_order'] ?? 0,
        ]);

        return redirect()->route('organization.index', ['period_id' => $period->id])
            ->with('success', __('app.org_position_added'));
    }

    /**
     * PUT /organization/positions/{position}
     */
    public function updatePosition(Request $request, OrganizationPosition $position): RedirectResponse
    {
        $this->authorizeAction('organization.edit');

        $data = $request->validate([
            'position_name'    => 'required|string|max:100',
            'parent_id'        => 'nullable|string|max:36',
            'resident_id'      => 'nullable|string|max:36',
            'householder_id'   => 'nullable|string|max:36',
            'sort_order'       => 'nullable|integer|min:0',
        ]);

        $newParentId = $data['parent_id'] ?? null;

        // Prevent self-reference and circular hierarchy
        if ($newParentId) {
            if ($newParentId === $position->id) {
                return back()->withErrors(['parent_id' => __('app.org_circular_self')])->withInput();
            }
            if ($this->isAncestor($position->id, $newParentId)) {
                return back()->withErrors(['parent_id' => __('app.org_circular_ref')])->withInput();
            }
            if (!OrganizationPosition::where('id', $newParentId)
                    ->where('organization_period_id', $position->organization_period_id)->exists()) {
                return back()->withErrors(['parent_id' => __('app.org_invalid_parent')])->withInput();
            }
        }

        $error = $this->checkPersonConflict(
            $position->organization_period_id,
            $data['householder_id'] ?? null,
            $data['resident_id'] ?? null,
            $position->id
        );
        if ($error) return back()->withErrors($error)->withInput();

        $position->update([
            'position_name'    => $data['position_name'],
            'parent_id'        => $newParentId,
            'householder_id'   => $data['householder_id'] ?? null,
            'resident_id'      => $data['resident_id'] ?? null,
            'sort_order'       => $data['sort_order'] ?? $position->sort_order,
        ]);

        return redirect()->route('organization.index', ['period_id' => $position->organization_period_id])
            ->with('success', __('app.org_position_updated'));
    }

    /**
     * DELETE /organization/positions/{position}
     */
    public function destroyPosition(OrganizationPosition $position): RedirectResponse
    {
        $this->authorizeAction('organization.delete');

        $periodId = $position->organization_period_id;
        $position->delete(); // children's parent_id becomes null via DB constraint

        return redirect()->route('organization.index', ['period_id' => $periodId])
            ->with('success', __('app.org_position_deleted'));
    }

    // ── Member search (AJAX) ──────────────────────────────────────────────────

    /**
     * GET /organization/search-members?q=&period_id=
     */
    public function searchMembers(Request $request): JsonResponse
    {
        $q        = trim($request->get('q', ''));
        $periodId = $request->get('period_id');
        $excludeId = $request->get('exclude_position_id'); // used on edit to ignore self

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        // IDs already assigned in this period
        $usedResidents = [];
        if ($periodId) {
            $query = OrganizationPosition::where('organization_period_id', $periodId);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            $usedResidents = $query->whereNotNull('resident_id')->pluck('resident_id')->toArray();
        }

        $results = [];

        // Residents only
        Resident::with(['householder.block', 'householder.unit'])
            ->whereNotIn('id', $usedResidents)
            ->whereHas('householder', fn($r) => $r->where('is_active', true))
            ->where('fullname', 'like', "%{$q}%")
            ->limit(10)
            ->get()
            ->each(function ($fm) use (&$results) {
                $r = $fm->householder;
                $results[] = [
                    'id'       => $fm->id,
                    'type'     => 'resident',
                    'name'     => $fm->fullname,
                    'location' => ($r?->block?->name ?? '') . ($r?->unit_number ? ' · ' . $r->unit_number : ''),
                    'phone'    => '',
                    'photo'    => $fm->photoUrl(),
                ];
            });

        usort($results, fn($a, $b) => strcmp($a['name'], $b['name']));

        return response()->json(array_slice($results, 0, 15));
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Build a nested tree array from a flat Collection.
     * Uses in-memory recursion — single DB query for the whole period.
     */
    private function buildTree(Collection $positions, ?string $parentId = null): array
    {
        return $positions
            ->filter(fn($p) => $p->parent_id === $parentId)
            ->sortBy('sort_order')
            ->values()
            ->map(fn($p) => [
                'node'     => $p,
                'children' => $this->buildTree($positions, $p->id),
            ])
            ->toArray();
    }

    /**
     * Check if $positionId is an ancestor of $targetId.
     * Walks up the parent chain to detect circular references.
     * Safely handles corrupt data by tracking visited IDs.
     */
    private function isAncestor(string $positionId, string $targetId): bool
    {
        $visited = [];
        $current = OrganizationPosition::find($targetId);

        while ($current && $current->parent_id) {
            if (isset($visited[$current->id])) break;
            $visited[$current->id] = true;

            if ($current->parent_id === $positionId) return true;
            $current = OrganizationPosition::find($current->parent_id);
        }

        return false;
    }

    /**
     * Validate that the given resident/family member doesn't already hold
     * a position in the same period. Returns an error array or null.
     */
    private function checkPersonConflict(
        string $periodId,
        ?string $householderId,
        ?string $residentId,
        ?string $excludePositionId = null
    ): ?array {
        if ($householderId && $residentId) {
            return ['person' => __('app.org_person_conflict_both')];
        }

        if ($householderId) {
            $q = OrganizationPosition::where('organization_period_id', $periodId)
                ->where('householder_id', $householderId);
            if ($excludePositionId) $q->where('id', '!=', $excludePositionId);
            if ($q->exists()) {
                return ['householder_id' => __('app.org_person_conflict_resident')];
            }
        }

        if ($residentId) {
            $q = OrganizationPosition::where('organization_period_id', $periodId)
                ->where('resident_id', $residentId);
            if ($excludePositionId) $q->where('id', '!=', $excludePositionId);
            if ($q->exists()) {
                return ['resident_id' => __('app.org_person_conflict_member')];
            }
        }

        return null;
    }

    /**
     * Abort with 403 if the authenticated user lacks the given permission.
     */
    private function authorizeAction(string $permission): void
    {
        if (!auth()->user()->can($permission)) {
            abort(403, 'Unauthorized.');
        }
    }
}
