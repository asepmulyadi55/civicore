<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\MediaFile;
use App\Models\PropertyListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PropertyListingController extends Controller
{
    public function index(Request $request)
    {
        $query = PropertyListing::with(['block'])->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location_label', 'like', "%{$search}%")
                  ->orWhere('contact_name', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $listings = $query->paginate(15)->withQueryString();
        $blocks   = Block::where('is_active', true)->orderBy('name')->get();
        $total    = PropertyListing::count();

        return view('property', compact('listings', 'blocks', 'total'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:200',
            'type'           => 'required|in:sell,rent',
            'price'          => 'nullable|numeric|min:0',
            'block_id'       => 'nullable|exists:blocks,id',
            'unit_id'        => 'nullable|exists:units,id',
            'location_label' => 'nullable|string|max:200',
            'bedrooms'       => 'nullable|integer|min:0|max:99',
            'bathrooms'      => 'nullable|integer|min:0|max:99',
            'land_area'      => 'nullable|numeric|min:0',
            'building_area'  => 'nullable|numeric|min:0',
            'description'    => 'nullable|string|max:2000',
            'contact_name'   => 'nullable|string|max:150',
            'contact_phone'  => 'nullable|string|max:50',
            'images.*'       => 'nullable|image|max:5120',
            'status'         => 'required|in:available,sold,rented',
        ]);

        $imagePaths = [];
        foreach ($request->file('images', []) as $file) {
            $path = $file->store('property', 'public');
            $imagePaths[] = $path;
            MediaFile::create([
                'disk'          => 'public',
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getMimeType(),
                'size'          => $file->getSize(),
                'uploaded_by'   => Auth::id(),
            ]);
        }

        $validated['images']     = $imagePaths ?: null;
        $validated['is_active']  = $request->boolean('is_active', true);
        $validated['created_by'] = Auth::id();

        PropertyListing::create($validated);

        return redirect()->route('property.index')
            ->with('success', __('app.flash_property_created'));
    }

    public function update(Request $request, PropertyListing $property)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:200',
            'type'           => 'required|in:sell,rent',
            'price'          => 'nullable|numeric|min:0',
            'block_id'       => 'nullable|exists:blocks,id',
            'unit_id'        => 'nullable|exists:units,id',
            'location_label' => 'nullable|string|max:200',
            'bedrooms'       => 'nullable|integer|min:0|max:99',
            'bathrooms'      => 'nullable|integer|min:0|max:99',
            'land_area'      => 'nullable|numeric|min:0',
            'building_area'  => 'nullable|numeric|min:0',
            'description'    => 'nullable|string|max:2000',
            'contact_name'   => 'nullable|string|max:150',
            'contact_phone'  => 'nullable|string|max:50',
            'images.*'       => 'nullable|image|max:5120',
            'remove_images'  => 'nullable|array',
            'remove_images.*'=> 'string',
            'status'         => 'required|in:available,sold,rented',
        ]);

        // Remove flagged images
        $existingImages = $property->images ?? [];
        $removeList     = $request->input('remove_images', []);
        $keptImages     = array_values(array_filter($existingImages, fn($p) => !in_array($p, $removeList)));
        foreach ($removeList as $path) {
            Storage::disk('public')->delete($path);
            MediaFile::where('path', $path)->delete();
        }

        // Upload new images
        foreach ($request->file('images', []) as $file) {
            $path = $file->store('property', 'public');
            $keptImages[] = $path;
            MediaFile::create([
                'disk'          => 'public',
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getMimeType(),
                'size'          => $file->getSize(),
                'uploaded_by'   => Auth::id(),
            ]);
        }

        $validated['images']    = $keptImages ?: null;
        $validated['is_active'] = $request->boolean('is_active', true);
        unset($validated['remove_images']);

        $property->update($validated);

        $redirectTo = $request->input('_stay_on_show')
            ? route('property.show', $property)
            : route('property.index');

        return redirect($redirectTo)
            ->with('success', __('app.flash_property_updated'));
    }

    public function destroy(PropertyListing $property)
    {
        foreach ($property->images ?? [] as $path) {
            Storage::disk('public')->delete($path);
            MediaFile::where('path', $path)->delete();
        }
        $property->delete();

        return redirect()->route('property.index')
            ->with('success', __('app.flash_property_deleted'));
    }

    public function toggleActive(PropertyListing $property)
    {
        $property->update(['is_active' => !$property->is_active]);
        return back()->with('success', __('app.flash_property_updated'));
    }
}
