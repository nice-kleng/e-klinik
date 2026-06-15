@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Detail Pasien</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('patients.print-card', $patient) }}" class="btn btn-info" target="_blank">
                <i class="bi bi-printer"></i> Cetak Kartu
            </a>
            <a href="{{ route('patients.edit', $patient) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </div>

    @include('components.alert')

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;">
                        <span class="fs-1 text-primary">{{ substr($patient->name, 0, 1) }}</span>
                    </div>
                    <h5 class="mb-1">{{ $patient->name }}</h5>
                    <p class="text-muted mb-2">No. RM: <strong>{{ $patient->no_rm }}</strong></p>
                    @if($patient->bpjsPatient)
                        <span class="badge bg-success fs-6 mb-2">BPJS Aktif</span>
                    @else
                        <span class="badge bg-secondary fs-6 mb-2">Non BPJS</span>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Info Asuransi</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted">Jenis</td>
                            <td>{{ $patient->insurance_type ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">No. Asuransi</td>
                            <td>{{ $patient->insurance_number ?? '-' }}</td>
                        </tr>
                        @if($patient->bpjsPatient)
                            <tr>
                                <td class="text-muted">No. Kartu BPJS</td>
                                <td>{{ $patient->bpjsPatient->no_kartu }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Kelas</td>
                                <td>{{ $patient->bpjsPatient->hak_kelas ?? '-' }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Data Identitas</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted" style="width:180px">NIK</td>
                            <td>{{ $patient->nik }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">No. KK</td>
                            <td>{{ $patient->no_kk ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tempat, Tanggal Lahir</td>
                            <td>{{ $patient->birth_place }}, {{ $patient->birth_date?->format('d/m/Y') }} ({{ $patient->age }} tahun)</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jenis Kelamin</td>
                            <td>{{ $patient->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Golongan Darah</td>
                            <td>{{ $patient->blood_type ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Agama</td>
                            <td>{{ $patient->religion ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status Pernikahan</td>
                            <td>{{ $patient->marriage_status ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Pekerjaan</td>
                            <td>{{ $patient->occupation ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

                    <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Data Sosial</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted" style="width:180px">Pendidikan</td>
                            <td>{{ $patient->education ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nama Ibu Kandung</td>
                            <td>{{ $patient->mother_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Kontak Darurat</td>
                            <td>{{ $patient->emergency_contact ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Alergi</td>
                            <td>{{ $patient->allergy ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Alamat</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1">{{ $patient->address ?? '-' }}</p>
                    <p class="mb-0 text-muted">
                        RT {{ $patient->rt ?? '-' }} / RW {{ $patient->rw ?? '-' }},
                        {{ $patient->village ?? '-' }}, {{ $patient->district ?? '-' }},
                        {{ $patient->city ?? '-' }}, {{ $patient->province ?? '-' }}
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Kontak</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted" style="width:180px">Telepon</td>
                            <td>{{ $patient->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Email</td>
                            <td>{{ $patient->email ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Riwayat Kunjungan</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Poliklinik</th>
                                <th>Dokter</th>
                                <th>Diagnosis</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($visitHistory as $record)
                                <tr>
                                    <td>{{ $record->visit_date?->format('d/m/Y') }}</td>
                                    <td>{{ $record->polyclinic?->name ?? '-' }}</td>
                                    <td>{{ $record->doctor?->name ?? '-' }}</td>
                                    <td>{{ $record->diagnosis_primary ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('medical-records.show', $record) }}" class="btn btn-sm btn-info">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">Belum ada riwayat kunjungan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
