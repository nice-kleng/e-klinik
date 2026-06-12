<?php

namespace App\Models;

use App\Traits\HasCreatedBy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Registration extends Model
{
    use HasCreatedBy, HasFactory;

    protected $fillable = [
        'registration_number',
        'patient_id',
        'polyclinic_id',
        'doctor_id',
        'registration_date',
        'source',
        'service_status',
        'bpjs_antrian_id',
        'no_sep',
        'age_text',
        'age_years',
        'age_months',
        'age_days',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'registration_date' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function polyclinic(): BelongsTo
    {
        return $this->belongsTo(Polyclinic::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function queue(): HasOne
    {
        return $this->hasOne(Queue::class, 'registration_id');
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'registration_id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'registration_id');
    }

    public function labRequests(): HasMany
    {
        return $this->hasMany(LabRequest::class, 'registration_id');
    }

    public function bpjsAntrean(): BelongsTo
    {
        return $this->belongsTo(BpjsAntrean::class, 'bpjs_antrian_id', 'id');
    }

    public static function calculateAge(Carbon $birthDate, Carbon $asOf): array
    {
        $diff = $birthDate->diff($asOf);
        return [
            'text' => "{$diff->y} Tahun {$diff->m} Bulan {$diff->d} Hari",
            'years' => $diff->y,
            'months' => $diff->m,
            'days' => $diff->d,
        ];
    }

    public static function generateNumber(): string
    {
        $prefix = 'REG-' . now()->format('Ymd') . '-';
        $last = static::where('registration_number', 'like', "{$prefix}%")
            ->orderBy('registration_number', 'desc')
            ->first();

        if ($last && preg_match('/-(\d{4})$/', $last->registration_number, $m)) {
            $seq = (int) $m[1] + 1;
        } else {
            $seq = 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
