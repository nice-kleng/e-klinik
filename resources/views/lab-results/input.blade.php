@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center mb-3">
        <h4 class="mb-0">Input Hasil Lab - Permintaan #{{ $labRequest->id }}</h4>
        <a href="{{ route('lab-requests.show', $labRequest) }}" class="btn btn-outline-secondary ms-auto">Kembali</a>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold">
            Pasien: {{ $labRequest->patient->name ?? '-' }} ({{ $labRequest->patient->no_rm ?? '-' }})
        </div>
    </div>

    <form method="POST" action="{{ route('lab-results.store') }}">
        @csrf

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>Pemeriksaan</th>
                            <th>Spesimen</th>
                            <th>Satuan</th>
                            <th>Hasil</th>
                            <th>Nilai Rujukan</th>
                            <th>Flag</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($labRequest->items as $item)
                            @php $test = $item->labTest; @endphp
                            <tr>
                                <td>
                                    {{ $test->name ?? '-' }}
                                    <input type="hidden" name="results[{{ $loop->index }}][lab_request_item_id]" value="{{ $item->id }}">
                                    <input type="hidden" name="results[{{ $loop->index }}][lab_test_id]" value="{{ $test->id }}">
                                    <input type="hidden" name="results[{{ $loop->index }}][patient_id]" value="{{ $labRequest->patient_id }}">
                                </td>
                                <td>{{ $test->specimen_type ?? '-' }}</td>
                                <td>{{ $test->unit ?? '-' }}</td>
                                <td>
                                    <input type="text" name="results[{{ $loop->index }}][result_value]" class="form-control form-control-sm"
                                        value="{{ old("results.{$loop->index}.result_value", $item->result->result_value ?? '') }}"
                                        placeholder="Nilai hasil" maxlength="100">
                                </td>
                                <td>
                                    <div class="d-flex gap-1 align-items-center">
                                        <input type="text" name="results[{{ $loop->index }}][ref_range_low]" class="form-control form-control-sm"
                                            value="{{ old("results.{$loop->index}.ref_range_low", $item->result->ref_range_low ?? $test->ref_range_low ?? '') }}"
                                            placeholder="Low" style="width:70px">
                                        <span>-</span>
                                        <input type="text" name="results[{{ $loop->index }}][ref_range_high]" class="form-control form-control-sm"
                                            value="{{ old("results.{$loop->index}.ref_range_high", $item->result->ref_range_high ?? $test->ref_range_high ?? '') }}"
                                            placeholder="High" style="width:70px">
                                    </div>
                                    <input type="text" name="results[{{ $loop->index }}][ref_range_text]" class="form-control form-control-sm mt-1"
                                        value="{{ old("results.{$loop->index}.ref_range_text", $item->result->ref_range_text ?? $test->ref_range_text ?? '') }}"
                                        placeholder="Teks rujukan">
                                    <input type="hidden" name="results[{{ $loop->index }}][unit]" value="{{ $test->unit ?? '' }}">
                                </td>
                                <td>
                                    <select name="results[{{ $loop->index }}][flag]" class="form-select form-select-sm" required>
                                        <option value="not_tested">Belum Diperiksa</option>
                                        <option value="normal" @selected(($item->result->flag ?? '') == 'normal')>Normal</option>
                                        <option value="abnormal" @selected(($item->result->flag ?? '') == 'abnormal')>Abnormal</option>
                                        <option value="critical" @selected(($item->result->flag ?? '') == 'critical')>Critical</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="results[{{ $loop->index }}][notes]" class="form-control form-control-sm"
                                        value="{{ old("results.{$loop->index}.notes", $item->result->notes ?? '') }}"
                                        placeholder="Catatan">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-success">Simpan Hasil</button>
            <a href="{{ route('lab-requests.show', $labRequest) }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
