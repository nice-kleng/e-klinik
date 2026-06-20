# TTE (Tanda Tangan Elektronik) — e-Klinik

## Overview

Implementasi TTE menggunakan pendekatan **Hash SHA-256 + QR Code** (Fase 1) tanpa biaya tambahan. Nantinya bisa di-upgrade ke provider tersertifikasi (Privy/Vida/BSrE) di Fase 2.

## Arsitektur

```
[Dokter klik "Tanda Tangani"]
        │
        ▼
[TteService::sign()]
  ├── Baca seluruh field kritis RME
  ├── Generate SHA-256 hash
  ├── Simpan: signed_by, signed_at, signature_hash
  └── Return hash
        │
        ▼
[PDF Generation]
  ├── Hash dikonversi ke QR Code
  ├── QR Code + metadata TTE di-render di PDF
  └── PDF di-stream ke browser
        │
        ▼
[Verifikasi]
  ├── Scan QR Code → buka /verify/{hash}
  ├── System lookup hash di DB
  └── Tampilkan SAH / TIDAK SAH
```

## Database

### Migration — `add_tte_fields_to_medical_records`

```php
Schema::table('medical_records', function (Blueprint $table) {
    $table->foreignId('signed_by')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();

    $table->timestamp('signed_at')->nullable();

    $table->string('signature_hash', 64)
        ->nullable()
        ->unique()
        ->comment('SHA-256 hash dari konten RME');

    $table->boolean('is_tte_verified')
        ->default(false)
        ->comment('Status verifikasi TTE');
});
```

### Kolom yang di-hash

Field-field berikut digabung (concatenate) + di-hash dengan SHA-256:

```
patient_id + visit_date + subjective_complaint + anamnesis +
objective_finding + physical_exam + assessment + differential_diagnosis +
plan + diagnosis_primary_id + json(diagnosis_secondary_ids) +
json(procedure_ids) + notes + created_at
```

Rumus: `hash('sha256', implode('|', [$fields]))`

## Service

### `app/Services/TteService.php`

#### Methods

| Method | Parameters | Return | Description |
|--------|-----------|--------|-------------|
| `sign()` | MedicalRecord $mr, User $user | array{signed_by, signed_at, signature_hash} | Generate hash + simpan TTE |
| `verify()` | string $hash | ?MedicalRecord | Cari MR by hash, return null jika tidak ditemukan |
| `verifyIntegrity()` | MedicalRecord $mr | bool | Generate ulang hash dari konten saat ini, bandingkan dengan yang tersimpan |
| `generateHash()` | MedicalRecord $mr | string | Generate SHA-256 hash dari field-field kritis |
| `getSignedUrl()` | MedicalRecord $mr | string | URL verifikasi |

#### Alur `sign()`

```php
public function sign(MedicalRecord $mr, User $user): array
{
    if ($mr->signed_by) {
        throw new \RuntimeException('RME sudah ditandatangani sebelumnya');
    }

    $hash = $this->generateHash($mr);

    $mr->update([
        'signed_by' => $user->id,
        'signed_at' => now(),
        'signature_hash' => $hash,
        'is_tte_verified' => true,
    ]);

    return [
        'signed_by' => $user->id,
        'signed_at' => $mr->signed_at,
        'signature_hash' => $hash,
    ];
}
```

#### Alur `generateHash()`

```php
public function generateHash(MedicalRecord $mr): string
{
    $data = [
        $mr->patient_id,
        $mr->visit_date?->format('Y-m-d'),
        $mr->subjective_complaint,
        $mr->anamnesis,
        $mr->objective_finding,
        $mr->physical_exam,
        $mr->assessment,
        $mr->differential_diagnosis,
        $mr->plan,
        $mr->diagnosis_primary_id,
        json_encode($mr->diagnosis_secondary_ids ?? []),
        json_encode($mr->procedure_ids ?? []),
        $mr->notes,
        $mr->created_at?->toIso8601String(),
    ];

    return hash('sha256', implode('|', $data));
}
```

## Controller

### Tambahan di `MedicalRecordController`

#### `sign(Request $request, MedicalRecord $medicalRecord)`

- Method: POST
- Middleware: `auth`, `role:admin|doctor`
- Panggil `TteService::sign($mr, auth()->user())`
- Return: JSON `{ success: true, data: { signature_hash, signed_at } }`
- Redirect: back() dengan flash success

#### `verifyPdf($hash)`

- Method: GET
- Cari MR by hash via `TteService::verify()`
- Jika ditemukan: tampilkan halaman verifikasi dengan data MR
- Jika tidak: tampilkan "Dokumen tidak ditemukan atau tidak sah"
- Return: view `medical-records.tte-verify`

#### `downloadPdf(MedicalRecord $medicalRecord)`

- Method: GET
- Generate PDF dengan QR code + metadata TTE
- Return: stream PDF

## Routes

```php
// TTE routes
Route::middleware(['auth', 'role:admin|doctor'])->group(function () {
    Route::post('/medical-records/{medicalRecord}/sign', [MedicalRecordController::class, 'sign'])
        ->name('medical-records.sign');

    Route::get('/medical-records/{medicalRecord}/pdf', [MedicalRecordController::class, 'downloadPdf'])
        ->name('medical-records.pdf');
});

// Public verification route (no auth — bisa diakses via QR scan)
Route::get('/medical-records/verify/{hash}', [MedicalRecordController::class, 'verifyPdf'])
    ->name('medical-records.verify');
```

## Views

### `show.blade.php` — Status TTE + Tombol Sign

```blade
{{-- Di kolom kiri, setelah tombol aksi cepat --}}
@if($mr->signed_by)
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-body text-center">
            <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
            <h6 class="text-success mb-1">✓ Ditandatangani</h6>
            <small class="text-muted">
                {{ $mr->signer?->name }}<br>
                {{ $mr->signed_at?->format('d/m/Y H:i') }}
            </small>
            <div class="mt-2">
                <a href="{{ route('medical-records.pdf', $mr) }}" class="btn btn-sm btn-success">
                    <i class="fas fa-file-pdf me-1"></i>Download PDF TTE
                </a>
            </div>
        </div>
    </div>
@else
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-body text-center">
            <i class="fas fa-file-signature fa-2x text-muted mb-2"></i>
            <h6 class="mb-1">Belum Ditandatangani</h6>
            <button class="btn btn-primary btn-sm" onclick="signRme({{ $mr->id }})">
                <i class="fas fa-pen me-1"></i>Tanda Tangani Sekarang
            </button>
        </div>
    </div>
@endif
```

### `workspace.blade.php` — Tab status TTE
- Tambahkan badge di header

### `pdf/rekam-medis.blade.php` — PDF dengan QR Code

```blade
@php
    $hash = $medicalRecord->signature_hash;
    $qrCodeSvg = QrCode::size(80)->generate(route('medical-records.verify', $hash));
@endphp

<div class="footer">
    <div class="qr-code">
        {!! $qrCodeSvg !!}
    </div>
    <div class="signature-info">
        <p><strong>Hash:</strong> {{ $hash }}</p>
        <p><strong>Ditandatangani oleh:</strong> {{ $medicalRecord->signer?->name }}</p>
        <p><strong>Tanggal:</strong> {{ $medicalRecord->signed_at?->format('d/m/Y H:i:s') }}</p>
        <p><em>Dokumen ini sah dan ditandatangani secara elektronik</em></p>
    </div>
</div>
```

### `medical-records/tte-verify.blade.php` — Halaman Verifikasi

```
Layout:
- Jika SAH: icon hijau centang, data RME (pasien, dokter, tanggal, diagnosis)
- Jika TIDAK SAH: icon merah silang, "Dokumen tidak ditemukan atau hash tidak valid"
- QR code bisa di-scan ulang
```

## Model Relations

### `MedicalRecord.php`

```php
public function signer(): BelongsTo
{
    return $this->belongsTo(User::class, 'signed_by');
}
```

### Fillable additions

```php
'signed_by', 'signed_at', 'signature_hash', 'is_tte_verified',
```

## Biaya

| Komponen | Biaya | Keterangan |
|----------|-------|------------|
| QR Code library | Gratis | sudah terinstall |
| DomPDF | Gratis | sudah terinstall |
| SHA-256 hash | Gratis | PHP built-in |
| **Total Fase 1** | **Rp 0** | |

## Fase 2 — Integrasi Provider (Opsional)

Jika diperlukan pengakuan legal yang lebih kuat:

| Provider | Model Biaya | Integrasi |
|----------|-------------|-----------|
| BSrE (Pemerintah) | Gratis | API + sertifikat digital |
| PrivyID | Pay-per-use / monthly | REST API + QR verify |
| Vida | Pay-per-use / monthly | REST API + OTP |

Arsitektur sudah siap: `TteService` bisa di-extend dengan provider tanpa mengubah flow controller.

## File yang Berubah

| File | Tipe | Perubahan |
|------|------|-----------|
| `database/migrations/xxxx_xx_xx_xxxxxx_add_tte_fields_to_medical_records.php` | Baru | Migration |
| `app/Services/TteService.php` | Baru | Service |
| `app/Http/Controllers/Web/MedicalRecordController.php` | Edit | +3 method |
| `app/Models/MedicalRecord.php` | Edit | +fillable + relasi |
| `routes/web.php` | Edit | +3 route |
| `resources/views/medical-records/show.blade.php` | Edit | +card TTE |
| `resources/views/medical-records/workspace.blade.php` | Edit | +badge TTE |
| `resources/views/pdf/rekam-medis.blade.php` | Edit | +QR code + hash |
| `resources/views/medical-records/tte-verify.blade.php` | Baru | Verifikasi |
