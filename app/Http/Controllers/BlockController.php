<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Resident;
use Illuminate\Http\Request;

class BlockController extends Controller
{
    public function index(Request $request)
    {
        $blocks = Block::withCount([
            'residents',
            'residents as active_residents_count' => fn($q) => $q->where('is_active', true),
            'units',
            'units as occupied_units_count' => fn($q) => $q->whereHas('resident'),
        ])
            ->with([
                'coordinators' => fn($q) => $q->select('id', 'name', 'block_id', 'role_id')
                    ->whereHas('role', fn($r) => $r->where('name', 'block_coordinator'))
            ])
            ->orderBy('name')
            ->get();

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
        $residentCount = $block->residents()->count();
        if ($residentCount > 0) {
            return redirect()->route('blocks.index')
                ->with('error', "Cannot delete \"{$block->name}\" — it has {$residentCount} resident(s) linked to it. Reassign or remove them first.");
        }

        $name = $block->name;
        $block->delete();

        return redirect()->route('blocks.index')->with('success', "\"{$name}\" has been deleted.");
    }
}
