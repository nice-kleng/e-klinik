@extends('layouts.volt')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if($medicalRecord)
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-check-circle fa-4x text-success"></i>
                        </div>
                        <h3 class="text-success mb-2">✓ Dokumen SAH</h3>
                        <p class="text-muted mb-4">Tanda tangan elektronik telah terverifikasi</p>

                        <div class="text-start bg-light rounded p-4 mb-4">
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td class="text-muted" style="width:140px">Hash</td>
                                    <td><code class="small">{{ $medicalRecord->signature_hash }}</code></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Pasien</td>
                                    <td><strong>{{ $medicalRecord->patient?->name ?? '-' }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">No. RM</td>
                                    <td>{{ $medicalRecord->patient?->no_rm ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Dokter</td>
                                    <td>{{ $medicalRecord->doctor?->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Poliklinik</td>
                                    <td>{{ $medicalRecord->polyclinic?->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tanggal</td>
                                    <td>{{ $medicalRecord->visit_date?->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">TTE oleh</td>
                                    <td>{{ $medicalRecord->signer?->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Waktu TTE</td>
                                    <td>{{ $medicalRecord->signed_at?->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>

                        <p class="text-muted small mb-0">
                            Dokumen ini telah ditandatangani secara elektronik menggunakan SHA-256.
                            <br>Setiap perubahan pada dokumen akan merusak hash dan membatalkan keabsahan TTE.
                        </p>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-times-circle fa-4x text-danger"></i>
                        </div>
                        <h3 class="text-danger mb-2">✗ Dokumen TIDAK SAH</h3>
                        <p class="text-muted">
                            Dokumen tidak ditemukan atau hash tidak valid.
                            <br>Hubungi petugas administrasi untuk verifikasi lebih lanjut.
                        </p>
                        <div class="mt-4">
                            <a href="{{ url('/') }}" class="btn btn-outline-secondary">Kembali ke Beranda</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
