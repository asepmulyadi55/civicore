<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Householder;
use Illuminate\Http\Request;

class HouseholderController extends Controller
{
    /**
     * AJAX — check if a householder exists by email (for user approval modal).
     *
     * POST /users/check-resident-email
     */
    public function checkEmail(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $householder = Householder::where('email', $request->email)->with('block')->first();

        if (!$householder) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found'       => true,
            'block_id'    => $householder->block_id,
            'block_name'  => $householder->block?->name ?? '—',
            'unit_number' => $householder->unit_number,
        ]);
    }
}
