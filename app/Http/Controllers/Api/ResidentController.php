<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use Illuminate\Http\Request;

class ResidentController extends Controller
{
    /**
     * AJAX — check if a resident exists by email (for user approval modal).
     *
     * POST /users/check-resident-email
     */
    public function checkEmail(Request $request)
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
}
