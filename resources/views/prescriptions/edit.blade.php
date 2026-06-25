@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Edit Resep — {{ $prescription->prescription_number }}</h4>
        <a href="{{ route('prescriptions.show', $prescription) }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    @include('components.alert')

    <form method="POST" action="{{ route('prescriptions.update', $prescription) }}" id="prescriptionForm">
        @csrf
        @method('PUT')
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Data Resep</h5>
                <div class="form-check form-switch mb-0">
                    <input type="checkbox" class="form-check-input" id="autoCalcToggle" {{ config('pharmacy.auto_calc', true) ? 'checked' : '' }}>
                    <label class="form-check-label small" for="autoCalcToggle">Auto-kalkulasi</label>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">No. Resep</label>
                        <input type="text" class="form-control" value="{{ $prescription->prescription_number }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal</label>
                        <input type="text" class="form-control" value="{{ $prescription->prescription_date?->format('d/m/Y') }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Pasien</label>
                        <input type="text" class="form-control" value="{{ $prescription->patient->name ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes', $prescription->notes) }}</textarea>
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
                            <th>Racikan</th>
                            <th width="60">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($prescription->items as $i => $item)
                            <tr>
                                <td>
                                    <select name="items[{{ $i }}][medicine_id]" class="form-select" required>
                                        <option value="">-- Pilih Obat --</option>
                                        @foreach($medicines as $med)
                                            <option value="{{ $med->id }}" {{ $item->medicine_id == $med->id ? 'selected' : '' }}>{{ $med->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $i }}][quantity]" class="form-control" value="{{ $item->quantity }}" min="1" required>
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $i }}][dosage]" class="form-control" value="{{ is_array($item->dosage) ? json_encode($item->dosage) : $item->dosage }}" placeholder="3x1 sehari sesudah makan">
                                </td>
                                <td>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input is-compound-toggle" name="items[{{ $i }}][is_compound]" value="1" {{ $item->is_compound ? 'checked' : '' }}>
                                        <label class="form-check-label small">Racikan</label>
                                    </div>
                                    <input type="hidden" name="items[{{ $i }}][compound_name]" value="{{ $item->compound_name }}">
                                    <input type="hidden" name="items[{{ $i }}][total_packets]" value="{{ $item->total_packets }}">
                                    <input type="hidden" name="items[{{ $i }}][instruction]" value="{{ $item->instruction }}">
                                    <input type="hidden" name="items[{{ $i }}][unit]" value="{{ $item->unit }}">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger remove-item">Hapus</button>
                                </td>
                            </tr>
                            @if($item->is_compound && $item->ingredients->isNotEmpty())
                            <tr class="compound-row">
                                <td colspan="5" class="bg-light p-2">
                                    <div class="ms-4">
                                        <div class="d-flex gap-2 mb-2 align-items-center">
                                            <input type="text" class="form-control form-control-sm compound-name" style="width:150px" placeholder="Nama racikan" value="{{ $item->compound_name }}">
                                            <input type="number" class="form-control form-control-sm compound-packets" style="width:120px" placeholder="Jumlah bungkus" value="{{ $item->total_packets }}" min="1">
                                            <input type="text" class="form-control form-control-sm compound-instruction" style="width:220px" placeholder="Aturan pakai" value="{{ $item->instruction }}">
                                        </div>
                                        <table class="table table-sm table-borderless mb-0" style="width:auto">
                                            <thead><tr><th>Bahan</th><th>Per Bungkus</th><th>Butuh</th><th>Aksi</th></tr></thead>
                                            <tbody class="ingredients-body">
                                                @foreach($item->ingredients as $j => $ing)
                                                <tr>
                                                    <td>
                                                        <select name="items[{{ $i }}][ingredients][{{ $j }}][medicine_id]" class="form-select form-select-sm ingredient-medicine" style="width:200px" required>
                                                            <option value="">-- Pilih --</option>
                                                            @foreach($medicines as $med)
                                                                <option value="{{ $med->id }}" data-dosage="{{ $med->dosage_per_unit ?? 0 }}" {{ $ing->medicine_id == $med->id ? 'selected' : '' }}>{{ $med->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="items[{{ $i }}][ingredients][{{ $j }}][qty_per_packet]" class="form-control form-control-sm ingredient-qty" style="width:100px" step="0.01" value="{{ $ing->qty_per_packet }}" required>
                                                        <select name="items[{{ $i }}][ingredients][{{ $j }}][unit]" class="form-select form-select-sm d-inline" style="width:70px">
                                                            <option value="mg" {{ $ing->unit == 'mg' ? 'selected' : '' }}>mg</option>
                                                            <option value="g" {{ $ing->unit == 'g' ? 'selected' : '' }}>g</option>
                                                            <option value="ml" {{ $ing->unit == 'ml' ? 'selected' : '' }}>ml</option>
                                                            <option value="tbl" {{ $ing->unit == 'tbl' ? 'selected' : '' }}>tbl</option>
                                                            <option value="kap" {{ $ing->unit == 'kap' ? 'selected' : '' }}>kap</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="items[{{ $i }}][ingredients][{{ $j }}][calculated_qty]" class="ingredient-calc-qty" value="{{ $ing->calculated_qty }}">
                                                        <span class="ingredient-calc-display small text-primary fw-bold">{{ $ing->calculated_qty ? 'Butuh '.$ing->calculated_qty.' tablet' : '' }}</span>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-danger remove-ingredient">X</button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <button type="button" class="btn btn-sm btn-outline-primary add-ingredient mt-1">+ Bahan</button>
                                    </div>
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('prescriptions.show', $prescription) }}" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const medicines = @json($medicines->map(fn($m) => ['id' => $m->id, 'name' => $m->name, 'dosage_per_unit' => $m->dosage_per_unit]));

    function getAutoCalcState() {
        return document.getElementById('autoCalcToggle')?.checked ?? true;
    }

    function calcIngredient(tr) {
        if (!getAutoCalcState()) return;

        const medSelect = tr.querySelector('.ingredient-medicine');
        const qtyInput = tr.querySelector('.ingredient-qty');
        const calcHidden = tr.querySelector('.ingredient-calc-qty');
        const calcDisplay = tr.querySelector('.ingredient-calc-display');

        const compoundRow = tr.closest('.compound-row');
        const packetsInput = compoundRow?.querySelector('.compound-packets');
        const totalPackets = parseFloat(packetsInput?.value) || 0;

        const qtyPerPacket = parseFloat(qtyInput?.value) || 0;
        const medId = parseInt(medSelect?.value) || 0;
        const med = medicines.find(m => m.id === medId);
        const dosagePerUnit = parseFloat(med?.dosage_per_unit) || 0;

        if (qtyPerPacket > 0 && totalPackets > 0 && dosagePerUnit > 0) {
            const calc = Math.ceil((qtyPerPacket * totalPackets) / dosagePerUnit);
            calcHidden.value = calc;
            calcDisplay.textContent = `Butuh ${calc} tablet`;
        } else {
            calcHidden.value = '';
            calcDisplay.textContent = dosagePerUnit === 0 && medId ? '(isi dosis/satuan di master obat)' : '';
        }
    }

    function recalcCompound(compoundRow) {
        compoundRow.querySelectorAll('.ingredients-body tr').forEach(tr => calcIngredient(tr));
    }

    document.getElementById('addItem')?.addEventListener('click', function() {
        const tbody = document.querySelector('#itemsTable tbody');
        const index = tbody.querySelectorAll('tr:not(.compound-row)').length;
        let options = '<option value="">-- Pilih Obat --</option>';
        medicines.forEach(m => { options += `<option value="${m.id}">${m.name}</option>`; });
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><select name="items[${index}][medicine_id]" class="form-select" required>${options}</select></td>
            <td><input type="number" name="items[${index}][quantity]" class="form-control" min="1" required></td>
            <td><input type="text" name="items[${index}][dosage]" class="form-control" placeholder="3x1 sehari sesudah makan"></td>
            <td>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input is-compound-toggle" name="items[${index}][is_compound]" value="1">
                    <label class="form-check-label small">Racikan</label>
                </div>
                <input type="hidden" name="items[${index}][compound_name]" value="">
                <input type="hidden" name="items[${index}][total_packets]" value="">
                <input type="hidden" name="items[${index}][instruction]" value="">
                <input type="hidden" name="items[${index}][unit]" value="pcs">
            </td>
            <td><button type="button" class="btn btn-sm btn-danger remove-item">Hapus</button></td>
        `;
        tbody.appendChild(tr);
    });

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('is-compound-toggle')) {
            const tr = e.target.closest('tr');
            const tbody = tr.closest('tbody');
            const index = Array.from(tbody.querySelectorAll('tr:not(.compound-row)')).indexOf(tr);
            let nextRow = tr.nextElementSibling;
            if (nextRow && nextRow.classList.contains('compound-row')) nextRow.remove();

            if (e.target.checked) {
                const div = document.createElement('div');
                div.className = 'ms-4';
                div.innerHTML = `
                    <div class="d-flex gap-2 mb-2 align-items-center">
                        <input type="text" class="form-control form-control-sm compound-name" style="width:150px" placeholder="Nama racikan" value="">
                        <input type="number" class="form-control form-control-sm compound-packets" style="width:120px" placeholder="Jumlah bungkus" value="" min="1">
                        <input type="text" class="form-control form-control-sm compound-instruction" style="width:220px" placeholder="Aturan pakai" value="">
                    </div>
                    <table class="table table-sm table-borderless mb-0" style="width:auto">
                        <thead><tr><th>Bahan</th><th>Per Bungkus</th><th>Butuh</th><th>Aksi</th></tr></thead>
                        <tbody class="ingredients-body"></tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-primary add-ingredient mt-1">+ Bahan</button>
                `;
                const cr = document.createElement('tr');
                cr.className = 'compound-row';
                cr.innerHTML = '<td colspan="5" class="bg-light p-2"></td>';
                cr.querySelector('td').appendChild(div);
                tr.insertAdjacentElement('afterend', cr);
            }
        }

        if (e.target.classList.contains('ingredient-medicine')) {
            const compoundRow = e.target.closest('.compound-row');
            if (compoundRow) recalcCompound(compoundRow);
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('ingredient-qty') || e.target.classList.contains('compound-packets')) {
            const compoundRow = e.target.closest('.compound-row');
            if (compoundRow) recalcCompound(compoundRow);
        }
    });

    document.getElementById('prescriptionForm')?.addEventListener('submit', function() {
        document.querySelectorAll('.compound-row').forEach(row => {
            const tbody = row.closest('tbody');
            const itemRows = Array.from(tbody.querySelectorAll('tr:not(.compound-row)'));
            const index = itemRows.indexOf(row.previousElementSibling);
            if (index < 0) return;
            const div = row.querySelector('.ms-4');
            const name = `items[${index}]`;
            row.querySelector(`[name="${name}[compound_name]"]`).value = div.querySelector('.compound-name')?.value || '';
            row.querySelector(`[name="${name}[total_packets]"]`).value = div.querySelector('.compound-packets')?.value || '';
            row.querySelector(`[name="${name}[instruction]"]`).value = div.querySelector('.compound-instruction')?.value || '';
            const ingBodies = div.querySelectorAll('.ingredients-body');
            ingBodies.forEach(body => {
                body.querySelectorAll('tr').forEach((ingRow, j) => {
                    ingRow.querySelectorAll('select, input').forEach(el => {
                        const oldName = el.getAttribute('name');
                        if (oldName) el.setAttribute('name', oldName.replace(/items\[\d+\]/g, name).replace(/ingredients\[\d+\]/g, `ingredients[${j}]`));
                    });
                });
            });
        });
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-ingredient')) {
            const body = e.target.closest('.ms-4').querySelector('.ingredients-body');
            const compoundRow = e.target.closest('.compound-row');
            const tbody = compoundRow.closest('tbody');
            const index = Array.from(tbody.querySelectorAll('tr:not(.compound-row)')).indexOf(compoundRow.previousElementSibling);
            const ingIndex = body.querySelectorAll('tr').length;
            let options = '<option value="">-- Pilih --</option>';
            medicines.forEach(m => { options += `<option value="${m.id}" data-dosage="${m.dosage_per_unit || 0}">${m.name}</option>`; });
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><select name="items[${index}][ingredients][${ingIndex}][medicine_id]" class="form-select form-select-sm ingredient-medicine" style="width:200px" required>${options}</select></td>
                <td>
                    <input type="number" name="items[${index}][ingredients][${ingIndex}][qty_per_packet]" class="form-control form-control-sm ingredient-qty" style="width:100px" step="0.01" required>
                    <select name="items[${index}][ingredients][${ingIndex}][unit]" class="form-select form-select-sm d-inline" style="width:70px">
                        <option value="mg">mg</option><option value="g">g</option><option value="ml">ml</option><option value="tbl">tbl</option><option value="kap">kap</option>
                    </select>
                </td>
                <td>
                    <input type="hidden" name="items[${index}][ingredients][${ingIndex}][calculated_qty]" class="ingredient-calc-qty" value="">
                    <span class="ingredient-calc-display small text-primary fw-bold"></span>
                </td>
                <td><button type="button" class="btn btn-sm btn-outline-danger remove-ingredient">X</button></td>
            `;
            body.appendChild(tr);
        }
        if (e.target.classList.contains('remove-ingredient')) e.target.closest('tr')?.remove();
        if (e.target.classList.contains('remove-item')) {
            const tr = e.target.closest('tr');
            let next = tr.nextElementSibling;
            if (next && next.classList.contains('compound-row')) next.remove();
            tr.remove();
        }
    });
</script>
@endpush
