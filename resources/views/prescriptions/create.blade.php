@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Tambah Resep</h4>
        <a href="{{ route('prescriptions.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    @include('components.alert')

    <form method="POST" action="{{ route('prescriptions.store') }}" id="prescriptionForm">
        @csrf
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Data Resep</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Rekam Medis <span class="text-danger">*</span></label>
                        <select name="medical_record_id" class="form-select @error('medical_record_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Rekam Medis --</option>
                            @foreach($medicalRecords as $mr)
                                <option value="{{ $mr->id }}" {{ old('medical_record_id', $selectedMedicalRecordId) == $mr->id ? 'selected' : '' }}>
                                    {{ $mr->visit_date?->format('d/m/Y') }} - {{ $mr->patient->name ?? '' }} ({{ $mr->doctor->name ?? '' }})
                                </option>
                            @endforeach
                        </select>
                        @error('medical_record_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes') }}</textarea>
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Item Resep</h5>
                <button type="button" class="btn btn-sm btn-primary" id="addItem">+ Tambah Item</button>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0" id="itemsTable">
                    <thead>
                        <tr>
                            <th>Obat</th>
                            <th>Jumlah</th>
                            <th>Aturan Pakai</th>
                            <th width="60">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(old('items'))
                            @foreach(old('items') as $i => $item)
                                <tr>
                                    <td>
                                        <select name="items[{{ $i }}][medicine_id]" class="form-select" required>
                                            <option value="">-- Pilih Obat --</option>
                                            @foreach($medicines as $med)
                                                <option value="{{ $med->id }}" {{ $item['medicine_id'] == $med->id ? 'selected' : '' }}>{{ $med->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $i }}][quantity]" class="form-control" value="{{ $item['quantity'] }}" min="1" required>
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $i }}][dosage]" class="form-control" value="{{ $item['dosage'] }}" placeholder="3x1 sehari sesudah makan">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger remove-item">Hapus</button>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
                @error('items') <div class="text-danger small p-2">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('prescriptions.index') }}" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('addItem')?.addEventListener('click', function() {
        const tbody = document.querySelector('#itemsTable tbody');
        const index = tbody.querySelectorAll('tr').length;
        const medicines = @json($medicines->map(fn($m) => ['id' => $m->id, 'name' => $m->name]));
        let options = '<option value="">-- Pilih Obat --</option>';
        medicines.forEach(m => { options += `<option value="${m.id}">${m.name}</option>`; });

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <select name="items[${index}][medicine_id]" class="form-select" required>
                    ${options}
                </select>
            </td>
            <td>
                <input type="number" name="items[${index}][quantity]" class="form-control" min="1" required>
            </td>
            <td>
                <input type="text" name="items[${index}][dosage]" class="form-control" placeholder="3x1 sehari sesudah makan">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-item">Hapus</button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('tr')?.remove();
        }
    });
</script>
@endpush
