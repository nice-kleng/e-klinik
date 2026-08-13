@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Pengaturan</h4>
    </div>

    @include('components.alert')

    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('settings.update') }}">
                @csrf

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Auto-Kalkulasi Racikan</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="auto_calc" value="1"
                                id="autoCalc" {{ ($settings->get('auto_calc')?->value ?? 'true') === 'true' ? 'checked' : '' }}>
                            <label class="form-check-label" for="autoCalc">
                                Aktifkan auto-kalkulasi kebutuhan tablet
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1">
                            Saat aktif, jumlah tablet tiap bahan racikan dihitung otomatis dari rumus
                            <code>ceil(qty_per_packet × total_packets / dosage_per_unit)</code>.
                            Dokter tetap bisa override nilai hasil kalkulasi.
                        </small>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Biaya Racik</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Tuslah (Jasa Racik) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="tuslah" class="form-control @error('tuslah') is-invalid @enderror"
                                        value="{{ old('tuslah', $settings->get('tuslah')?->value ?? config('pharmacy.tuslah')) }}"
                                        min="0" max="99999999" required>
                                    @error('tuslah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <small class="text-muted">Biaya jasa peracikan per resep racikan</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Embalase (Biaya Bungkus) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="embalase" class="form-control @error('embalase') is-invalid @enderror"
                                        value="{{ old('embalase', $settings->get('embalase')?->value ?? config('pharmacy.embalase')) }}"
                                        min="0" max="99999999" required>
                                    @error('embalase') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <small class="text-muted">Biaya wadah/bungkus per resep racikan</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Biaya Pelayanan</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Biaya Konsultasi <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="biaya_konsultasi" class="form-control @error('biaya_konsultasi') is-invalid @enderror"
                                        value="{{ old('biaya_konsultasi', $settings->get('biaya_konsultasi')?->value ?? config('billing.biaya_konsultasi')) }}"
                                        min="0" max="99999999" required>
                                    @error('biaya_konsultasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <small class="text-muted">Biaya jasa konsultasi dokter per kunjungan. Otomatis masuk ke invoice saat pasien sampai ke kasir.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-1"></i>Informasi</h6>
                </div>
                <div class="card-body small">
                    <p class="mb-2"><strong>Auto-kalkulasi</strong><br>
                    Rumus: <code>ceil(qty × bungkus / dosis_satuan)</code><br>
                    Contoh: Paracetamol 250mg × 15 bks / 500mg = 8 tablet</p>

                    <p class="mb-2"><strong>Tuslah</strong><br>
                    Biaya jasa peracikan obat. Dikenakan sekali per resep racikan.</p>

                    <p class="mb-0"><strong>Embalase</strong><br>
                    Biaya wadah/bungkus untuk obat racikan. Dikenakan sekali per resep racikan.</p>

                    <hr>
                    <p class="text-muted mb-0">
                        <i class="fas fa-database me-1"></i> Nilai tersimpan di database.
                        Perubahan berlaku langsung tanpa perlu restart.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
