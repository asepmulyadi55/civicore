<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Unit;
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
                'coordinators' => fn($q) => $q->select('id', 'name', 'block_id', 'role_id')
                    ->whereHas('role', fn($r) => $r->where('name', 'block_coordinator'))
            ])
            ->orderBy('name');

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $blocks = $query->get();

        return view('blocks', compact('blocks'));
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

        return redirect()->route('blocks.index')->with('success', "Block \"{$data['name']}\" has been added.");
    }

    /**
     * Import blocks and units from an Excel file.
     *
     * Expected Excel layout (IuranWarga sheet):
     *   Col F = Block letter (e.g. A, B, C …)
     *   Col G = Unit number  (e.g. 1, 3, 5 …)
     *   Col J = Status (Pemilik / Pemilik Kosong / Pengontrak / Kavling …)
     * Data starts at row 4. Merged-cell issues are avoided by reading col F & G
     * (raw per-row values) instead of the merged C & D columns.
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
            'pemilik kosong'    => 'vacant',
            'kavling'           => 'vacant',
            'pengontrak'        => 'rented',
            'developer'         => 'vacant',
            'warga'             => 'owner_occupied',
        ];

        $spreadsheet = IOFactory::load($request->file('excel_file')->getRealPath());
        $sheet       = $spreadsheet->getSheet(0);
        $maxRow      = $sheet->getHighestRow();

        $blocksCreated  = 0;
        $blocksSkipped  = 0;
        $unitsCreated   = 0;
        $unitsSkipped   = 0;
        $blockCache     = [];   // letter => Block model

        for ($row = 4; $row <= $maxRow; $row++) {
            $blockLetter = strtoupper(trim($sheet->getCell('F' . $row)->getCalculatedValue() ?? ''));
            $unitNum     = trim($sheet->getCell('G' . $row)->getCalculatedValue() ?? '');
            $rawStatus   = strtolower(trim(preg_replace('/\s+/', ' ',
                $sheet->getCell('J' . $row)->getCalculatedValue() ?? ''
            )));

            // Skip blanks, header repeats, and common-area rows
            if ($blockLetter === '' || $unitNum === '') continue;
            if (in_array($rawStatus, ['fasum', 'fasilitasumum'])) continue;
            if (!ctype_alpha($blockLetter)) continue;  // guard against stray text

            // ── Block ──────────────────────────────────────────────────
            if (!isset($blockCache[$blockLetter])) {
                [$block, $created] = [
                    Block::firstOrCreate(
                        ['name' => $blockLetter],
                        ['is_active' => true]
                    ),
                    false,
                ];
                $created = $block->wasRecentlyCreated;
                $blockCache[$blockLetter] = $block;
                $created ? $blocksCreated++ : $blocksSkipped++;
            }

            $block = $blockCache[$blockLetter];

            // ── Unit ───────────────────────────────────────────────────
            $houseStatus = $statusMap[$rawStatus] ?? 'owner_occupied';

            $unit = Unit::firstOrCreate(
                ['block_id' => $block->id, 'unit_number' => $unitNum],
                ['house_status' => $houseStatus, 'is_active' => true]
            );

            $unit->wasRecentlyCreated ? $unitsCreated++ : $unitsSkipped++;
        }

        $summary = "Import complete — "
            . "{$blocksCreated} block(s) created, {$blocksSkipped} already existed | "
            . "{$unitsCreated} unit(s) created, {$unitsSkipped} already existed.";

        return redirect()->route('blocks.index')->with('success', $summary);
    }

    public function update(Request $request, Block $block)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', "unique:blocks,name,{$block->id}"],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Please enter a block name.',
            'name.unique' => 'A block with this name already exists.',
        ]);

        // $request->boolean() correctly returns false when checkbox is absent (unchecked)
        $block->update(array_merge($data, [
            'is_active' => $request->boolean('is_active'),
        ]));

        return redirect()->route('blocks.index')->with('success', "Block \"{$block->name}\" has been updated.");
    }

    public function destroy(Block $block)
    {
        $residentCount = $block->householders()->count();
        if ($residentCount > 0) {
            return redirect()->route('blocks.index')
                ->with('error', "Cannot delete \"{$block->name}\" — it has {$residentCount} resident(s) linked to it. Reassign or remove them first.");
        }

        $name = $block->name;
        $block->delete();

        return redirect()->route('blocks.index')->with('success', "\"{$name}\" has been deleted.");
    }
}
