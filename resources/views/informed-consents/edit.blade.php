@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    @include('components.alert')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Edit Informed Consent #{{ $informedConsent->id }}</h4>
        <a href="{{ route('informed-consents.show', $informedConsent) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <form action="{{ route('informed-consents.update', $informedConsent) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white"><h6 class="mb-0">Data Informed Consent</h6></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Pasien</label>
                            <input type="text" class="form-control" value="{{ $informedConsent->patient?->name }} ({{ $informedConsent->patient?->no_rm ?? '-' }})" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipe Informed Consent <span class="text-danger">*</span></label>
                            <select name="consent_type" class="form-select @error('consent_type') is-invalid @enderror" required>
                                @foreach($consentTypes as $val => $label)
                                    <option value="{{ $val }}" {{ old('consent_type', $informedConsent->consent_type) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('consent_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Tindakan / Prosedur</label>
                            <input type="text" name="procedure_name" class="form-control" value="{{ old('procedure_name', $informedConsent->procedure_name) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kode ICD-9-CM (Prosedur)</label>
                            <select name="procedure_icd9_id" class="form-select select2-icd9 @error('procedure_icd9_id') is-invalid @enderror">
                                <option value="">— Cari Kode ICD-9 —</option>
                                @if($informedConsent->procedureIcd9)
                                    <option value="{{ $informedConsent->procedure_icd9_id }}" selected>
                                        {{ $informedConsent->procedureIcd9->code }} — {{ $informedConsent->procedureIcd9->name }}
                                    </option>
                                @endif
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Diagnosis</label>
                            <textarea name="diagnosis" class="form-control" rows="2">{{ old('diagnosis', $informedConsent->diagnosis) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tujuan Tindakan</label>
                            <textarea name="purpose" class="form-control" rows="2">{{ old('purpose', $informedConsent->purpose) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Risiko</label>
                            <textarea name="risks" class="form-control" rows="2">{{ old('risks', $informedConsent->risks) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Manfaat</label>
                            <textarea name="benefits" class="form-control" rows="2">{{ old('benefits', $informedConsent->benefits) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alternatif Tindakan</label>
                            <textarea name="alternatives" class="form-control" rows="2">{{ old('alternatives', $informedConsent->alternatives) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Rekomendasi Dokter</label>
                            <textarea name="doctor_recommendation" class="form-control" rows="2">{{ old('doctor_recommendation', $informedConsent->doctor_recommendation) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Saksi</label>
                            <input type="text" name="witness_name" class="form-control" value="{{ old('witness_name', $informedConsent->witness_name) }}">
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
                                    <option value="{{ $proc->id }}" @if($informedConsent->procedures->contains('id', $proc->id)) selected @endif>
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
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="fas fa-save me-1"></i>Perbarui
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
                    var mrId = '{{ $informedConsent->medical_record_id }}';
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
