<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    protected $fillable = ['name', 'label', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function paymentRecords(): HasMany
    {
        return $this->hasMany(PaymentRecord::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
