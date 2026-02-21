<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Resident;
use Illuminate\Http\Request;

class BlockController extends Controller
{
    public function index(Request $request)
    {
        $blocks = Block::withCount(['residents', 'residents as active_residents_count' => fn($q) => $q->where('is_active', true)])
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
            'is_active' => ['boolean'],
        ], [
            'name.required' => 'Please enter a block name.',
            'name.unique' => 'A block with this name already exists.',
        ]);

        $block->update($data);

        return redirect()->route('blocks.index')->with('success', "Block \"{$block->name}\" has been updated.");
    }

    public function destroy(Block $block)
    {
        $residentCount = $block->residents()->where('is_active', true)->count();
        if ($residentCount > 0) {
            return redirect()->route('blocks.index')
                ->with('error', "Cannot deactivate \"{$block->name}\" — it has {$residentCount} active resident(s).");
        }

        $block->update(['is_active' => false]);

        return redirect()->route('blocks.index')->with('success', "\"{$block->name}\" has been deactivated.");
    }
}
