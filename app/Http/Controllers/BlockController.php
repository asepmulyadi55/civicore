<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class BlockController extends Controller
{
    public function index(Request $request)
    {
        $query = Block::withCount([
            'householders',
            'householders as active_residents_count' => fn($q) => $q->where('is_active', true),
            'units',
            'units as owner_occupied_units_count' => fn($q) => $q->where('house_status', 'owner_occupied'),
            'units as rented_units_count'         => fn($q) => $q->where('house_status', 'rented'),
            'units as vacant_units_count'         => fn($q) => $q->where('house_status', 'vacant'),
        ])
            ->with([
                'coordinators' => fn($q) => $q->select('users.id', 'users.name')
                    ->whereHas('role', fn($r) => $r->where('name', 'block_coordinator'))
            ])
            ->orderBy('name');

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $blocks = $query->get();

        // All block coordinators available for assignment
        $coordinatorUsers = User::whereHas('role', fn($q) => $q->where('name', 'block_coordinator'))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('blocks', compact('blocks', 'coordinatorUsers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:blocks,name'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Please enter a block name.',
            'name.unique' => 'A block with this name already exists.',
        ]);

        Block::create(array_merge($data, ['is_active' => true]));

        return redirect()->route('blocks.index')->with('success', __('app.flash_block_added', ['name' => $data['name']]));
    }

    /**
     * Import blocks and units from an Excel file.
     *
     * Expected Excel layout:
     *   Col A = Block letter (e.g. A, B, C …) - Can be blank, will use previous row's block
     *   Col B = Unit number  (e.g. 1, 3, 5 …)
     *   Col D = Status (Pemilik / Pemilik Kosong / Pengontrak / Kavling …)
     * Data starts at row 2.
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ], [
            'excel_file.required' => 'Please choose an Excel file to upload.',
            'excel_file.mimes'    => 'Only .xlsx and .xls files are accepted.',
        ]);

        // ── Map STATUS WARGA → house_status enum ──────────────────────────
        $statusMap = [
            'pemilik'           => 'owner_occupied',
            'pemilik/kosong'    => 'vacant',
            'pemilik kosong'    => 'vacant',
            'kavling'           => 'vacant',
            'pengontrak'        => 'rented',
            'developer'         => 'vacant',
            'warga'             => 'owner_occupied',
            'fasum'             => 'public_facility',
            'fasilitasumum'     => 'public_facility',
            ''                  => 'vacant',
        ];

        $spreadsheet = IOFactory::load($request->file('excel_file')->getRealPath());
        $sheet       = $spreadsheet->getSheet(0);
        $maxRow      = $sheet->getHighestRow();

        $blocksCreated  = 0;
        $blocksSkipped  = 0;
        $unitsCreated   = 0;
        $unitsSkipped   = 0;
        $blockCache     = [];   // letter => Block model

        $lastBlockLetter = '';

        for ($row = 2; $row <= $maxRow; $row++) {
            $blockLetter = strtoupper(trim($sheet->getCell('A' . $row)->getCalculatedValue() ?? ''));
            if ($blockLetter !== '') {
                $lastBlockLetter = $blockLetter;
            } else {
                $blockLetter = $lastBlockLetter;
            }

            $unitNum     = trim($sheet->getCell('B' . $row)->getCalculatedValue() ?? '');
            $rawStatus   = strtolower(trim(preg_replace('/\s+/', ' ',
                $sheet->getCell('D' . $row)->getCalculatedValue() ?? ''
            )));

            // Skip blanks, header repeats
            if ($blockLetter === '' || $unitNum === '') continue;

            // ── Block ──────────────────────────────────────────────────
            if (!isset($blockCache[$blockLetter])) {
                $block = Block::firstOrCreate(
                    ['name' => $blockLetter],
                    ['is_active' => true]
                );
                
                if (!$block->wasRecentlyCreated && !$block->is_active) {
                    $block->update(['is_active' => true]);
                }

                $created = $block->wasRecentlyCreated;
                $blockCache[$blockLetter] = $block;
                $created ? $blocksCreated++ : $blocksSkipped++;
            }

            $block = $blockCache[$blockLetter];

            // ── Unit ───────────────────────────────────────────────────
            $houseStatus = $statusMap[$rawStatus] ?? 'vacant';

            $unit = Unit::firstOrCreate(
                ['block_id' => $block->id, 'unit_number' => $unitNum],
                ['house_status' => $houseStatus, 'is_active' => true]
            );

            if (!$unit->wasRecentlyCreated) {
                $unit->update([
                    'house_status' => $houseStatus,
                    'is_active'    => true,
                ]);
                $unitsSkipped++;
            } else {
                $unitsCreated++;
            }
        }

        $summary = "Import complete — "
            . "{$blocksCreated} block(s) created, {$blocksSkipped} already existed | "
            . "{$unitsCreated} unit(s) created, {$unitsSkipped} already existed.";

        return redirect()->route('blocks.index')->with('success', $summary);
    }

    public function update(Request $request, Block $block)
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:100', "unique:blocks,name,{$block->id}"],
            'description'      => ['nullable', 'string', 'max:255'],
            'is_active'        => ['nullable', 'boolean'],
            'coordinator_ids'  => ['nullable', 'array'],
            'coordinator_ids.*'=> ['exists:users,id'],
        ], [
            'name.required' => 'Please enter a block name.',
            'name.unique'   => 'A block with this name already exists.',
        ]);

        $block->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active'   => $request->boolean('is_active'),
        ]);

        // Sync assigned coordinators (empty array = remove all)
        $block->coordinators()->sync($data['coordinator_ids'] ?? []);

        return redirect()->route('blocks.index')->with('success', __('app.flash_block_updated', ['name' => $block->name]));
    }

    public function destroy(Block $block)
    {
        $residentCount = $block->householders()->count();
        if ($residentCount > 0) {
            return redirect()->route('blocks.index')
                ->with('error', __('app.flash_block_delete_residents', ['name' => $block->name, 'count' => $residentCount]));
        }

        $name = $block->name;
        $block->delete();

        return redirect()->route('blocks.index')->with('success', __('app.flash_block_deleted', ['name' => $name]));
    }
}
