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

        @if($triage)
        <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #17a2b8;">
            <div class="card-header bg-white">
                <h5 class="mb-0">Tanda-Tanda Vital (Dari Triage)</h5>
            </div>
            <div class="card-body py-2">
                <div class="row g-1 small">
                    <div class="col-md-2">TD: <strong>{{ $triage->systolic }}/{{ $triage->diastolic }}</strong> mmHg</div>
                    <div class="col-md-2">Nadi: <strong>{{ $triage->heart_rate }}</strong> /menit</div>
                    <div class="col-md-2">Suhu: <strong>{{ $triage->temperature }}</strong> &deg;C</div>
                    <div class="col-md-2">RR: <strong>{{ $triage->respiratory_rate }}</strong> /menit</div>
                    <div class="col-md-2">SpO₂: <strong>{{ $triage->oxygen_saturation }}</strong> %</div>
                    <div class="col-md-2">BB: <strong>{{ $triage->weight }}</strong> kg</div>
                </div>
            </div>
        </div>
        @endif

        {{-- S: Subjective --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">S — Subjective</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Keluhan Utama (Riwayat Penyakit Sekarang) <span class="text-danger">*</span></label>
                        <textarea name="subjective_complaint" class="form-control @error('subjective_complaint') is-invalid @enderror" rows="4">{{ old('subjective_complaint') }}</textarea>
                        @error('subjective_complaint') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Anamnesis</label>
                        <textarea name="anamnesis" class="form-control @error('anamnesis') is-invalid @enderror" rows="4">{{ old('anamnesis') }}</textarea>
                        @error('anamnesis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Riwayat Penyakit Dahulu</label>
                        <textarea name="past_history" class="form-control @error('past_history') is-invalid @enderror" rows="3">{{ old('past_history') }}</textarea>
                        @error('past_history') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Riwayat Pengobatan</label>
                        <textarea name="medication_history" class="form-control @error('medication_history') is-invalid @enderror" rows="3">{{ old('medication_history') }}</textarea>
                        @error('medication_history') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- O: Objective --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">O — Objective (Pemeriksaan Fisik)</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Pemeriksaan Fisik Umum</label>
                        <textarea name="objective_finding" class="form-control @error('objective_finding') is-invalid @enderror" rows="4">{{ old('objective_finding') }}</textarea>
                        @error('objective_finding') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pemeriksaan Fisik Detail</label>
                        <textarea name="physical_exam" class="form-control @error('physical_exam') is-invalid @enderror" rows="4">{{ old('physical_exam') }}</textarea>
                        @error('physical_exam') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">TTV (Re-check Dokter) — <small class="text-muted">Nilai default dari triage perawat, sesuaikan jika ada perubahan</small></label>
                        <div class="row g-2">
                            <div class="col-md-2">
                                <input type="text" name="vital_signs[blood_pressure]" class="form-control form-control-sm" value="{{ old('vital_signs.blood_pressure', $triage ? $triage->systolic.'/'.$triage->diastolic : '') }}" placeholder="TD (120/80)">
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="vital_signs[heart_rate]" class="form-control form-control-sm" value="{{ old('vital_signs.heart_rate', $triage?->heart_rate) }}" placeholder="Nadi">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="vital_signs[temperature]" class="form-control form-control-sm" value="{{ old('vital_signs.temperature', $triage?->temperature) }}" placeholder="Suhu">
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="vital_signs[respiratory_rate]" class="form-control form-control-sm" value="{{ old('vital_signs.respiratory_rate', $triage?->respiratory_rate) }}" placeholder="RR">
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="vital_signs[oxygen_saturation]" class="form-control form-control-sm" value="{{ old('vital_signs.oxygen_saturation', $triage?->oxygen_saturation) }}" placeholder="SpO₂">
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="vital_signs[blood_glucose]" class="form-control form-control-sm" value="{{ old('vital_signs.blood_glucose', $triage?->blood_glucose) }}" placeholder="Gula Darah">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Specialist-specific form --}}
        @if(isset($specialistPartial) && $specialistPartial)
            @include($specialistPartial)
        @endif

        {{-- A: Assessment --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">A — Assessment & Diagnosis</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Assessment <span class="text-danger">*</span></label>
                        <textarea name="assessment" class="form-control @error('assessment') is-invalid @enderror" rows="3">{{ old('assessment') }}</textarea>
                        @error('assessment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Diagnosis Banding (ICD-10)</label>
                        <select name="diagnosis_differential_ids[]" id="diagnosisDifferential" class="form-select select2-icd10" multiple
                            data-ajax-url="{{ route('medical-records.icd10-search') }}"
                            data-placeholder="Cari diagnosis banding...">
                        </select>
                        @error('diagnosis_differential_ids') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Diagnosis Utama (ICD-10) <span class="text-danger">*</span></label>
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

                </div>
            </div>
        </div>

        {{-- P: Plan --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">P — Plan (Rencana)</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Rencana Tatalaksana</label>
                        <textarea name="plan" class="form-control @error('plan') is-invalid @enderror" rows="3">{{ old('plan') }}</textarea>
                        @error('plan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tanggal Follow-up</label>
                        <input type="date" name="follow_up_date" class="form-control @error('follow_up_date') is-invalid @enderror" value="{{ old('follow_up_date') }}">
                        @error('follow_up_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes') }}</textarea>
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
