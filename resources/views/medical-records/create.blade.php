@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Tambah Rekam Medis</h4>
        <a href="{{ route('medical-records.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    @include('components.alert')

    <form method="POST" action="{{ route('medical-records.store') }}" id="formRme">
        @csrf
        @if($queueId)
            <input type="hidden" name="queue_id" value="{{ $queueId }}">
        @endif
        @if($registrationId)
            <input type="hidden" name="registration_id" value="{{ $registrationId }}">
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Data Pasien</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Pasien <span class="text-danger">*</span></label>
                        <select name="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Pasien --</option>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}" {{ (old('patient_id', $selectedPatientId) == $patient->id) ? 'selected' : '' }}>
                                    [{{ $patient->no_rm }}] {{ $patient->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('patient_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Dokter <span class="text-danger">*</span></label>
                        <select name="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Dokter --</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ old('doctor_id', $selectedDoctorId) == $doctor->id ? 'selected' : '' }}>
                                    {{ $doctor->name }} ({{ $doctor->polyclinic->name ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        @error('doctor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Poliklinik <span class="text-danger">*</span></label>
                        <select name="polyclinic_id" class="form-select @error('polyclinic_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Poliklinik --</option>
                            @foreach($polyclinics as $poly)
                                <option value="{{ $poly->id }}" {{ old('polyclinic_id', $selectedPolyclinicId) == $poly->id ? 'selected' : '' }}>{{ $poly->name }}</option>
                            @endforeach
                        </select>
                        @error('polyclinic_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Kunjungan <span class="text-danger">*</span></label>
                        <input type="date" name="visit_date" class="form-control @error('visit_date') is-invalid @enderror" value="{{ old('visit_date', date('Y-m-d')) }}" required>
                        @error('visit_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Jenis Kunjungan</label>
                        <input type="hidden" name="visit_type" value="{{ old('visit_type', $visitType ?? '') }}">
                        <div class="form-control-plaintext fw-medium">{{ old('visit_type', $visitType ?? '-') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Anamnesis & Pemeriksaan</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Anamnesis / Subjective</label>
                        <textarea name="anamnesis" class="form-control @error('anamnesis') is-invalid @enderror" rows="4">{{ old('anamnesis') }}</textarea>
                        @error('anamnesis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Keluhan Utama</label>
                        <textarea name="subjective_complaint" class="form-control @error('subjective_complaint') is-invalid @enderror" rows="2">{{ old('subjective_complaint') }}</textarea>
                        @error('subjective_complaint') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pemeriksaan Fisik / Objective</label>
                        <textarea name="objective_finding" class="form-control @error('objective_finding') is-invalid @enderror" rows="4">{{ old('objective_finding') }}</textarea>
                        @error('objective_finding') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pemeriksaan Fisik (Detail)</label>
                        <textarea name="physical_exam" class="form-control @error('physical_exam') is-invalid @enderror" rows="4">{{ old('physical_exam') }}</textarea>
                        @error('physical_exam') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Tanda-Tanda Vital</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tekanan Darah (mmHg)</label>
                        <input type="text" name="vital_signs[blood_pressure]" class="form-control" value="{{ old('vital_signs.blood_pressure') }}" placeholder="120/80">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Nadi (x/menit)</label>
                        <input type="number" name="vital_signs[heart_rate]" class="form-control" value="{{ old('vital_signs.heart_rate') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Suhu (&deg;C)</label>
                        <input type="text" name="vital_signs[temperature]" class="form-control" value="{{ old('vital_signs.temperature') }}" placeholder="36.5">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">RR (x/menit)</label>
                        <input type="number" name="vital_signs[respiratory_rate]" class="form-control" value="{{ old('vital_signs.respiratory_rate') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Berat Badan (kg)</label>
                        <input type="number" step="0.1" name="vital_signs[weight]" class="form-control" value="{{ old('vital_signs.weight') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tinggi Badan (cm)</label>
                        <input type="number" step="0.1" name="vital_signs[height]" class="form-control" value="{{ old('vital_signs.height') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">SpO&#8322; (%)</label>
                        <input type="number" name="vital_signs[oxygen_saturation]" class="form-control" value="{{ old('vital_signs.oxygen_saturation') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">GCS</label>
                        <input type="number" name="vital_signs[gcs]" class="form-control" value="{{ old('vital_signs.gcs') }}" placeholder="3-15" min="3" max="15">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Gula Darah (mg/dL)</label>
                        <input type="number" name="vital_signs[blood_glucose]" class="form-control" value="{{ old('vital_signs.blood_glucose') }}" placeholder="100">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Catatan TTV</label>
                        <textarea name="vital_signs[notes]" class="form-control" rows="2" placeholder="Catatan tambahan tentang tanda vital">{{ old('vital_signs.notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Diagnosis & Tindak Lanjut</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Assessment</label>
                        <textarea name="assessment" class="form-control @error('assessment') is-invalid @enderror" rows="3">{{ old('assessment') }}</textarea>
                        @error('assessment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Plan / Rencana</label>
                        <textarea name="plan" class="form-control @error('plan') is-invalid @enderror" rows="3">{{ old('plan') }}</textarea>
                        @error('plan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Diagnosis Utama (ICD-10)</label>
                        <select name="diagnosis_primary_id" id="diagnosisPrimary" class="form-select select2-icd10"
                            data-ajax-url="{{ route('medical-records.icd10-search') }}"
                            data-placeholder="Cari kode atau nama diagnosis...">
                            @if(old('diagnosis_primary_id'))
                                <option value="{{ old('diagnosis_primary_id') }}" selected>{{ old('diagnosis_primary_id') }}</option>
                            @endif
                        </select>
                        @error('diagnosis_primary_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Diagnosis Sekunder</label>
                        <select name="diagnosis_secondary_ids[]" id="diagnosisSecondary" class="form-select select2-icd10" multiple
                            data-ajax-url="{{ route('medical-records.icd10-search') }}"
                            data-placeholder="Cari diagnosis sekunder...">
                        </select>
                        @error('diagnosis_secondary_ids') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Prosedur / Tindakan (ICD-9-CM)</label>
                        <div id="proceduresContainer">
                            <div class="row g-2 mb-2 procedure-row">
                                <div class="col-md-6">
                                    <select name="procedure_ids[]" class="form-select select2-icd9"
                                        data-ajax-url="{{ route('medical-records.icd9-search') }}"
                                        data-placeholder="Cari prosedur...">
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="procedure_notes[]" class="form-control" placeholder="Catatan tindakan">
                                </div>
                                <div class="col-md-2 d-flex gap-1">
                                    <button type="button" class="btn btn-outline-success btn-add-procedure"><i class="fas fa-plus"></i></button>
                                    <button type="button" class="btn btn-outline-danger btn-remove-procedure" style="display:none;"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                        @error('procedure_ids') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes') }}</textarea>
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Follow-up</label>
                        <input type="date" name="follow_up_date" class="form-control @error('follow_up_date') is-invalid @enderror" value="{{ old('follow_up_date') }}">
                        @error('follow_up_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('medical-records.index') }}" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('proceduresContainer');
    const AJAX_URL = '{{ route('medical-records.icd9-search') }}';

    function makeSelect2(el) {
        $(el).select2({
            theme: 'bootstrap-5',
            placeholder: el.dataset.placeholder || 'Cari...',
            minimumInputLength: 2,
            allowClear: true,
            ajax: {
                url: AJAX_URL,
                dataType: 'json',
                delay: 300,
                data: function (p) { return { q: p.term }; },
                processResults: function (data) {
                    if (!Array.isArray(data)) { return { results: [] }; }
                    return { results: data.map(function (item) { return { id: item.id, text: item.code + ' — ' + item.name }; }) };
                },
                cache: true
            }
        });
    }

    function destroySelect2(el) {
        try {
            if ($(el).data('select2')) {
                $(el).select2('destroy');
            }
        } catch (_) {}
    }

    function createProcedureRow() {
        const html = '<div class="row g-2 mb-2 procedure-row">' +
            '<div class="col-md-6">' +
            '<select name="procedure_ids[]" class="form-select" data-placeholder="Cari prosedur..."></select>' +
            '</div>' +
            '<div class="col-md-4">' +
            '<input type="text" name="procedure_notes[]" class="form-control" placeholder="Catatan tindakan">' +
            '</div>' +
            '<div class="col-md-2 d-flex gap-1">' +
            '<button type="button" class="btn btn-outline-success btn-add-procedure"><i class="fas fa-plus"></i></button>' +
            '<button type="button" class="btn btn-outline-danger btn-remove-procedure"><i class="fas fa-times"></i></button>' +
            '</div>' +
            '</div>';
        const div = document.createElement('div');
        div.innerHTML = html;
        const row = div.firstElementChild;
        container.appendChild(row);
        const select = row.querySelector('select');
        row.querySelector('.btn-add-procedure').style.display = 'none';
        makeSelect2(select);
    }

    container.addEventListener('click', function (e) {
        if (e.target.closest('.btn-add-procedure')) {
            createProcedureRow();
        }

        if (e.target.closest('.btn-remove-procedure')) {
            const row = e.target.closest('.procedure-row');
            row.querySelectorAll('select').forEach(destroySelect2);
            row.remove();
        }
    });
});
</script>
@endpush
