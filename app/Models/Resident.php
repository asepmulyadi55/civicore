<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resident extends Model
{
    use HasUuids;

    protected $hidden = [
        // Temporary Data Hardening: completely hide these from UI/forms serialization.
        'nik',
        'phone',
        // 'no_kk' (family_card_number is on Householder model, but conceptually noted here)
    ];

    protected $fillable = [
        'householder_id', 'fullname', 'relationship', 'nik',
        'birth_date', 'gender', 'education', 'occupation', 'phone', 'is_head', 'photo_path',
    ];

    /**
     * SECURITY NOTE: `nik` and `no_kk` MUST be encrypted later when
     * admins need to read the actual numbers. They should NOT be hashed.
     * (Note: `nik` is already encrypted below).
     */
    protected $casts = [
        'birth_date' => 'encrypted', // Encrypted at rest
        'is_head'    => 'boolean',
        'nik'        => 'encrypted',
    ];

    public static array $relationships = [
        'head'   => 'Head of Family',
        'spouse' => 'Spouse',
        'child'  => 'Child',
        'parent' => 'Parent',
        'tenant' => 'Tenant',
        'other'  => 'Other',
    ];

    public static array $educationLevels = [
        'none'        => 'None / Not Stated',
        'elementary'  => 'Elementary School',
        'junior_high' => 'Junior High School',
        'senior_high' => 'Senior High School',
        'associate'   => 'Associate Degree (D1–D3)',
        'bachelor'    => "Bachelor's Degree (S1)",
        'master'      => "Master's Degree (S2)",
        'doctorate'   => 'Doctorate (S3)',
        'other'       => 'Other',
    ];

    public function householder(): BelongsTo
    {
        return $this->belongsTo(Householder::class, 'householder_id');
    }

    public function relationshipLabel(): string
    {
        return __('app.rel_' . ($this->relationship ?? 'other'));
    }

    public function educationLabel(): string
    {
        return __('app.edu_' . ($this->education ?? 'none'));
    }

    public function maskedNik(): string
    {
        if (!$this->nik) return '—';
        $val = $this->nik;
        return str_repeat('•', max(0, strlen($val) - 4)) . substr($val, -4);
    }

    public function photoUrl(): ?string
    {
        if (!$this->photo_path) return null;
        return route('private.file', ['path' => $this->photo_path]);
    }

    public function meetingAttendances(): HasMany
    {
        return $this->hasMany(MeetingAttendance::class);
    }

    /**
     * Accessor: Calculate Age based on the decrypted birth_date
     */
    protected function age(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn (mixed $value, array $attributes) => 
                isset($attributes['birth_date']) 
                    ? \Carbon\Carbon::parse($this->birth_date)->age 
                    : null,
        );
    }

    /**
     * Accessor: Determine Category based on the calculated age
     */
    protected function category(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function (mixed $value, array $attributes) {
                if (!isset($attributes['birth_date'])) {
                    return 'Unknown';
                }

                $age = $this->age;

                return match(true) {
                    $age < 3  => 'Baby',
                    $age < 6  => 'Toddler',
                    $age < 13 => 'Child',
                    $age < 20 => 'Teenager',
                    $age < 60 => 'Adult',
                    default   => 'Elder',
                };
            }
        );
    }
}

