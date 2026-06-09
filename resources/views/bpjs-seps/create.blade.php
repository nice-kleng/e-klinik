@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Buat SEP Baru</h4>
        <a href="{{ route('bpjs-seps.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('bpjs-seps.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Pasien BPJS <span class="text-danger">*</span></label>
                        <select name="patient_id" class="form-control @error('patient_id') is-invalid @enderror" required>
                            <option value="">Pilih Pasien</option>
                            @foreach($patients as $p)
                                <option value="{{ $p->id }}" {{ old('patient_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->no_rm }} - {{ $p->name }}
                                    @if($p->bpjsPatient)
                                        ({{ $p->bpjsPatient->no_kartu }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('patient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Antrean Terkait</label>
                        <select name="queue_id" class="form-control @error('queue_id') is-invalid @enderror">
                            <option value="">Tidak Ada</option>
                            @foreach($queues as $q)
                                <option value="{{ $q->id }}" {{ old('queue_id') == $q->id ? 'selected' : '' }}>
                                    {{ $q->queue_number }} - {{ $q->patient->name }} ({{ $q->polyclinic->name ?? '' }})
                                </option>
                            @endforeach
                        </select>
                        @error('queue_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tanggal Pelayanan <span class="text-danger">*</span></label>
                        <input type="date" name="tgl_pelayanan" class="form-control @error('tgl_pelayanan') is-invalid @enderror"
                            value="{{ old('tgl_pelayanan', now()->format('Y-m-d')) }}" required>
                        @error('tgl_pelayanan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Kode Poli <span class="text-danger">*</span></label>
                        <select name="kode_poli" class="form-control @error('kode_poli') is-invalid @enderror" required>
                            <option value="">Pilih Poli</option>
                            @foreach($polyclinics as $pol)
                                <option value="{{ $pol->code }}" {{ old('kode_poli') == $pol->code ? 'selected' : '' }}>
                                    {{ $pol->code }} - {{ $pol->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('kode_poli')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Kode Dokter</label>
                        <input type="text" name="kode_dokter" class="form-control @error('kode_dokter') is-invalid @enderror"
                            value="{{ old('kode_dokter') }}" placeholder="Kode dokter BPJS">
                        @error('kode_dokter')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Diagnosa (ICD-10)</label>
                        <input type="text" name="diagnosa" class="form-control @error('diagnosa') is-invalid @enderror"
                            value="{{ old('diagnosa') }}" placeholder="Contoh: J00">
                        @error('diagnosa')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">No. Rujukan</label>
                        <input type="text" name="no_rujukan" class="form-control @error('no_rujukan') is-invalid @enderror"
                            value="{{ old('no_rujukan') }}" placeholder="Jika ada">
                        @error('no_rujukan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan" class="form-control @error('catatan') is-invalid @enderror"
                            rows="2">{{ old('catatan') }}</textarea>
                        @error('catatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Simpan SEP</button>
                    <a href="{{ route('bpjs-seps.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
