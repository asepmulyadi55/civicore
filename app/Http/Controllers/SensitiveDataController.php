<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\Resident;
use Illuminate\Http\JsonResponse;

class SensitiveDataController extends Controller
{
    /**
     * Reveal the decrypted Family Card Number (No. KK) for a resident.
     * Admin-only AJAX endpoint.
     */
    public function revealFCN(Resident $resident): JsonResponse
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        return response()->json(['value' => $resident->family_card_number ?? '']);
    }

    /**
     * Reveal the decrypted NIK of a family member.
     * Admin-only AJAX endpoint.
     */
    public function revealNIK(Resident $resident, FamilyMember $familyMember): JsonResponse
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        if ($familyMember->resident_id !== $resident->id) {
            abort(404);
        }

        return response()->json(['value' => $familyMember->nik ?? '']);
    }
}
