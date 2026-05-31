<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PropertyListing extends Model
{
    use HasUuids;

    protected $fillable = [
        'title', 'type', 'price', 'location_label',
        'block_id', 'unit_id', 'bedrooms', 'bathrooms',
        'land_area', 'building_area', 'description',
        'contact_name', 'contact_phone', 'images',
        'status', 'is_active', 'created_by',
    ];

    protected $casts = [
        'images'        => 'array',
        'is_active'     => 'boolean',
        'bedrooms'      => 'integer',
        'bathrooms'     => 'integer',
        'price'         => 'decimal:0',
        'land_area'     => 'decimal:2',
        'building_area' => 'decimal:2',
    ];

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function imageUrls(): array
    {
        return array_map(
            fn($path) => Storage::disk('public')->url($path),
            $this->images ?? []
        );
    }

    public function typeLabel(): string
    {
        return __('app.property_type_' . $this->type);
    }

    public function statusLabel(): string
    {
        return __('app.property_status_' . $this->status);
    }

    public function formattedPrice(): string
    {
        if (!$this->price) return '—';
        $currency  = Setting::get('currency_symbol', 'Rp');
        $formatted = $currency . ' ' . number_format((float) $this->price, 0, ',', '.');
        return $this->type === 'rent' ? $formatted . '/bln' : $formatted;
    }
}
