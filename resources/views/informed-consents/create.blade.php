@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    @include('components.alert')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Buat Informed Consent</h4>
        <a href="{{ route('informed-consents.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <form action="{{ route('informed-consents.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white"><h6 class="mb-0">Data Informed Consent</h6></div>
                    <div class="card-body">
                        <input type="hidden" name="registration_id" value="{{ old('registration_id', $selectedRegistrationId) }}">
                        <input type="hidden" name="medical_record_id" value="{{ old('medical_record_id', $selectedMrId) }}">

                        <div class="mb-3">
                            <label class="form-label">Pasien <span class="text-danger">*</span></label>
                            <select name="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                                <option value="">— Pilih Pasien —</option>
                                @foreach($patients as $p)
                                    <option value="{{ $p->id }}" {{ old('patient_id', $selectedPatientId) == $p->id ? 'selected' : '' }}>
                                        {{ $p->no_rm }} — {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipe Informed Consent <span class="text-danger">*</span></label>
                            <select name="consent_type" class="form-select @error('consent_type') is-invalid @enderror" required>
                                <option value="">— Pilih Tipe —</option>
                                @foreach($consentTypes as $val => $label)
                                    <option value="{{ $val }}" {{ old('consent_type') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('consent_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Tindakan / Prosedur</label>
                            <input type="text" name="procedure_name" class="form-control @error('procedure_name') is-invalid @enderror" value="{{ old('procedure_name') }}" placeholder="Contoh: Ekstraksi Gigi">
                            @error('procedure_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kode ICD-9-CM (Prosedur)</label>
                            <select name="procedure_icd9_id" class="form-select select2-icd9 @error('procedure_icd9_id') is-invalid @enderror">
                                <option value="">— Cari Kode ICD-9 —</option>
                            </select>
                            @error('procedure_icd9_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Diagnosis</label>
                            <textarea name="diagnosis" class="form-control @error('diagnosis') is-invalid @enderror" rows="2">{{ old('diagnosis') }}</textarea>
                            @error('diagnosis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tujuan Tindakan</label>
                            <textarea name="purpose" class="form-control @error('purpose') is-invalid @enderror" rows="2">{{ old('purpose') }}</textarea>
                            @error('purpose')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Risiko</label>
                            <textarea name="risks" class="form-control @error('risks') is-invalid @enderror" rows="2">{{ old('risks') }}</textarea>
                            @error('risks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Manfaat</label>
                            <textarea name="benefits" class="form-control @error('benefits') is-invalid @enderror" rows="2">{{ old('benefits') }}</textarea>
                            @error('benefits')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alternatif Tindakan</label>
                            <textarea name="alternatives" class="form-control @error('alternatives') is-invalid @enderror" rows="2">{{ old('alternatives') }}</textarea>
                            @error('alternatives')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Rekomendasi Dokter</label>
                            <textarea name="doctor_recommendation" class="form-control @error('doctor_recommendation') is-invalid @enderror" rows="2">{{ old('doctor_recommendation') }}</textarea>
                            @error('doctor_recommendation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Saksi</label>
                            <input type="text" name="witness_name" class="form-control @error('witness_name') is-invalid @enderror" value="{{ old('witness_name') }}">
                            @error('witness_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white"><h6 class="mb-0">Prosedur Terkait</h6></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Pilih Prosedur dari RME</label>
                            <select name="procedure_ids[]" class="form-select select2-procedures" multiple>
                                @foreach($selectedProcedures as $proc)
                                    <option value="{{ $proc->id }}" selected>
                                        {{ $proc->icd9CmDiagnosis?->code ?? '' }} — {{ $proc->icd9CmDiagnosis?->name ?? 'Tindakan #' . $proc->id }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="small text-muted mt-1">Pilih prosedur yang memerlukan informed consent</div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-save me-1"></i>Simpan Draft
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2-icd9').select2({
            theme: 'bootstrap-5',
            placeholder: 'Cari kode ICD-9...',
            allowClear: true,
            width: '100%',
            ajax: {
                url: '{{ route("medical-records.icd9-search") }}',
                dataType: 'json',
                delay: 300,
                data: function(params) { return { q: params.term }; },
                processResults: function(data) {
                    return { results: data.data ? data.data.map(function(d) { return { id: d.id, text: d.code + ' — ' + d.name }; }) : [] };
                }
            }
        });

        $('.select2-procedures').select2({
            theme: 'bootstrap-5',
            placeholder: 'Cari prosedur...',
            width: '100%',
            ajax: {
                url: '{{ route("informed-consents.procedures") }}',
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    var mrId = $('input[name="medical_record_id"]').val();
                    return { q: params.term, medical_record_id: mrId };
                },
                processResults: function(data) {
                    return { results: data };
                }
            }
        });
    });
</script>
@endpush
