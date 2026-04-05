<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyMember extends Model
{
    use HasUuids;

    protected $fillable = [
        'resident_id', 'fullname', 'relationship', 'nik',
        'birth_date', 'gender', 'education', 'occupation', 'is_head', 'photo_path',
    ];

    protected $casts = [
        'birth_date' => 'date',
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

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function relationshipLabel(): string
    {
        return self::$relationships[$this->relationship] ?? 'Other';
    }

    public function educationLabel(): string
    {
        return self::$educationLevels[$this->education ?? 'none'] ?? '—';
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
}
