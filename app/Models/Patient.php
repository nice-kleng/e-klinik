<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'no_rm',
        'nik',
        'no_kk',
        'name',
        'birth_place',
        'birth_date',
        'gender',
        'blood_type',
        'address',
        'rt',
        'rw',
        'village',
        'district',
        'city',
        'province',
        'phone',
        'email',
        'occupation',
        'marriage_status',
        'religion',
        'insurance_type',
        'insurance_number',
        'bpjs_status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class, 'patient_id');
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'patient_id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'patient_id');
    }

    public function bpjsPatient(): HasOne
    {
        return $this->hasOne(BpjsPatient::class, 'patient_id');
    }

    public function bpjsClaims(): HasMany
    {
        return $this->hasMany(BpjsClaim::class, 'patient_id');
    }

    public function bpjsReferrals(): HasMany
    {
        return $this->hasMany(BpjsReferral::class, 'patient_id');
    }

    public function age(): Attribute
    {
        return Attribute::make(
            get: fn () => Carbon::parse($this->birth_date)->age,
        );
    }

    public function formattedRmNumber(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->no_rm,
        );
    }
}
