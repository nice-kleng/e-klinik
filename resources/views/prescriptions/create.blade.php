@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Tambah Resep</h4>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-info btn-sm" id="copyLastRx" style="display:none">
                <i class="fas fa-copy me-1"></i>Salin dari resep sebelumnya
            </button>
            <a href="{{ route('prescriptions.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </div>

    @include('components.alert')

    <form method="POST" action="{{ route('prescriptions.store') }}" id="prescriptionForm">
        @csrf
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
                    <div class="col-md-6">
                        <label class="form-label">Rekam Medis <span class="text-danger">*</span></label>
                        <select name="medical_record_id" class="form-select @error('medical_record_id') is-invalid @enderror" required id="medicalRecordSelect">
                            <option value="">-- Pilih Rekam Medis --</option>
                            @foreach($medicalRecords as $mr)
                                <option value="{{ $mr->id }}"
                                    data-patient-id="{{ $mr->patient_id }}"
                                    data-doctor-id="{{ $mr->doctor_id }}"
                                    {{ old('medical_record_id', $selectedMedicalRecordId) == $mr->id ? 'selected' : '' }}>
                                    {{ $mr->visit_date?->format('d/m/Y') }} - {{ $mr->patient->name ?? '' }} ({{ $mr->doctor->name ?? '' }})
                                </option>
                            @endforeach
                        </select>
                        @error('medical_record_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <input type="hidden" name="patient_id" id="patientIdField">
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
                            <th>Racikan</th>
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
                                        <input type="number" name="items[{{ $i }}][quantity]" class="form-control item-qty" value="{{ $item['quantity'] }}" min="1" required>
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $i }}][dosage]" class="form-control" value="{{ $item['dosage'] }}" placeholder="3x1 sehari sesudah makan">
                                    </td>
                                    <td>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input is-compound-toggle" name="items[{{ $i }}][is_compound]" value="1" {{ !empty($item['is_compound']) ? 'checked' : '' }}>
                                            <label class="form-check-label small">Racikan</label>
                                        </div>
                                        <input type="hidden" name="items[{{ $i }}][compound_name]" value="{{ $item['compound_name'] ?? '' }}">
                                        <input type="hidden" name="items[{{ $i }}][total_packets]" value="{{ $item['total_packets'] ?? '' }}">
                                        <input type="hidden" name="items[{{ $i }}][instruction]" value="{{ $item['instruction'] ?? '' }}">
                                        <input type="hidden" name="items[{{ $i }}][unit]" value="pcs">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger remove-item">Hapus</button>
                                    </td>
                                </tr>
                                @if(!empty($item['ingredients']))
                                <tr class="compound-row">
                                    <td colspan="5" class="bg-light p-2">
                                        <div class="ms-4">
                                            <div class="d-flex gap-2 mb-2 align-items-center">
                                                <input type="text" class="form-control form-control-sm compound-name" style="width:150px" placeholder="Nama racikan (puyer/kapsul)" value="{{ $item['compound_name'] ?? '' }}">
                                                <input type="number" class="form-control form-control-sm compound-packets" style="width:120px" placeholder="Jumlah bungkus" value="{{ $item['total_packets'] ?? '' }}" min="1">
                                                <input type="text" class="form-control form-control-sm compound-instruction" style="width:220px" placeholder="Aturan pakai racikan" value="{{ $item['instruction'] ?? '' }}">
                                            </div>
                                            <table class="table table-sm table-borderless mb-0" style="width:auto">
                                                <thead><tr><th>Bahan</th><th>Per Bungkus</th><th>Butuh</th><th>Aksi</th></tr></thead>
                                                <tbody class="ingredients-body">
                                                    @foreach($item['ingredients'] as $j => $ing)
                                                    <tr>
                                                        <td>
                                                            <select name="items[{{ $i }}][ingredients][{{ $j }}][medicine_id]" class="form-select form-select-sm ingredient-medicine" style="width:200px" required>
                                                                <option value="">-- Pilih --</option>
                                                                @foreach($medicines as $med)
                                                                    <option value="{{ $med->id }}" data-dosage="{{ $med->dosage_per_unit ?? 0 }}" {{ $ing['medicine_id'] == $med->id ? 'selected' : '' }}>{{ $med->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="number" name="items[{{ $i }}][ingredients][{{ $j }}][qty_per_packet]" class="form-control form-control-sm ingredient-qty" style="width:100px" step="0.01" value="{{ $ing['qty_per_packet'] }}" required>
                                                            <select name="items[{{ $i }}][ingredients][{{ $j }}][unit]" class="form-select form-select-sm d-inline" style="width:70px">
                                                                <option value="mg" {{ ($ing['unit'] ?? 'mg') == 'mg' ? 'selected' : '' }}>mg</option>
                                                                <option value="g" {{ ($ing['unit'] ?? '') == 'g' ? 'selected' : '' }}>g</option>
                                                                <option value="ml" {{ ($ing['unit'] ?? '') == 'ml' ? 'selected' : '' }}>ml</option>
                                                                <option value="tbl" {{ ($ing['unit'] ?? '') == 'tbl' ? 'selected' : '' }}>tbl</option>
                                                                <option value="kap" {{ ($ing['unit'] ?? '') == 'kap' ? 'selected' : '' }}>kap</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="hidden" name="items[{{ $i }}][ingredients][{{ $j }}][calculated_qty]" class="ingredient-calc-qty" value="{{ $ing['calculated_qty'] ?? '' }}">
                                                            <span class="ingredient-calc-display small text-primary fw-bold">{{ isset($ing['calculated_qty']) ? 'Butuh '.$ing['calculated_qty'].' tablet' : '' }}</span>
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
                        @endif
                    </tbody>
                </table>
                @error('items') <div class="text-danger small p-2">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('prescriptions.index') }}" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Resep</button>
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

    // ——— Auto-calc ingredient ———
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

    // ——— Recalc all ingredients in compound row ———
    function recalcCompound(compoundRow) {
        compoundRow.querySelectorAll('.ingredients-body tr').forEach(tr => calcIngredient(tr));
    }

    // ——— Copy last prescription (Obat Kronis) ———
    document.getElementById('medicalRecordSelect')?.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        const patientId = opt?.dataset?.patientId;
        document.getElementById('patientIdField').value = patientId || '';
        const btn = document.getElementById('copyLastRx');
        if (patientId) {
            btn.style.display = 'inline-block';
            btn.dataset.patientId = patientId;
        } else {
            btn.style.display = 'none';
        }
    });

    document.getElementById('copyLastRx')?.addEventListener('click', function() {
        const patientId = this.dataset.patientId;
        if (!patientId) return;

        fetch('{{ url('prescriptions/last') }}/' + patientId)
            .then(r => r.json())
            .then(res => {
                if (!res.success) {
                    alert(res.message);
                    return;
                }
                if (!confirm('Salin ' + res.data.items.length + ' item dari resep ' + res.data.prescription_number + ' (' + res.data.prescription_date + ')?')) return;

                const tbody = document.querySelector('#itemsTable tbody');
                tbody.innerHTML = '';
                res.data.items.forEach((item, i) => {
                    addRow(item, i);
                });
            })
            .catch(() => alert('Gagal mengambil data resep sebelumnya'));
    });

    // ——— Add item row ———
    document.getElementById('addItem')?.addEventListener('click', function() {
        const tbody = document.querySelector('#itemsTable tbody');
        const index = tbody.querySelectorAll('tr:not(.compound-row)').length;
        addRow(null, index);
    });

    function addRow(data, index) {
        const tbody = document.querySelector('#itemsTable tbody');
        const i = index;

        let options = '<option value="">-- Pilih Obat --</option>';
        medicines.forEach(m => { options += `<option value="${m.id}"${data?.medicine_id == m.id ? ' selected' : ''}>${m.name}</option>`; });

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <select name="items[${i}][medicine_id]" class="form-select" required>${options}</select>
            </td>
            <td>
                <input type="number" name="items[${i}][quantity]" class="form-control item-qty" value="${data?.quantity || ''}" min="1" required>
            </td>
            <td>
                <input type="text" name="items[${i}][dosage]" class="form-control" value="${data?.dosage || ''}" placeholder="3x1 sehari sesudah makan">
            </td>
            <td>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input is-compound-toggle" name="items[${i}][is_compound]" value="1" ${data?.is_compound ? 'checked' : ''}>
                    <label class="form-check-label small">Racikan</label>
                </div>
                <input type="hidden" name="items[${i}][compound_name]" value="${data?.compound_name || ''}">
                <input type="hidden" name="items[${i}][total_packets]" value="${data?.total_packets || ''}">
                <input type="hidden" name="items[${i}][instruction]" value="${data?.instruction || ''}">
                <input type="hidden" name="items[${i}][unit]" value="pcs">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-item">Hapus</button>
            </td>
        `;
        tbody.appendChild(tr);

        if (data?.is_compound && data?.ingredients?.length) {
            const compoundRow = buildCompoundRow(i, data);
            tbody.appendChild(compoundRow);
            document.querySelector(`[name="items[${i}][compound_name]"]`).value = data.compound_name || '';
            document.querySelector(`[name="items[${i}][total_packets]"]`).value = data.total_packets || '';
            document.querySelector(`[name="items[${i}][instruction]"]`).value = data.instruction || '';
            compoundRow.querySelector('.compound-name').value = data.compound_name || '';
            compoundRow.querySelector('.compound-packets').value = data.total_packets || '';
            compoundRow.querySelector('.compound-instruction').value = data.instruction || '';
            recalcCompound(compoundRow);
        }
    }

    // ——— Compound toggle ———
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('is-compound-toggle')) {
            const tr = e.target.closest('tr');
            const tbody = tr.closest('tbody');
            const index = Array.from(tbody.querySelectorAll('tr:not(.compound-row)')).indexOf(tr);

            let nextRow = tr.nextElementSibling;
            if (nextRow && nextRow.classList.contains('compound-row')) {
                nextRow.remove();
            }

            if (e.target.checked) {
                const compoundRow = buildCompoundRow(index, null);
                tr.insertAdjacentElement('afterend', compoundRow);
            }
        }
    });

    function buildCompoundRow(index, data) {
        const div = document.createElement('div');
        div.className = 'ms-4';
        div.innerHTML = `
            <div class="d-flex gap-2 mb-2 align-items-center">
                <input type="text" class="form-control form-control-sm compound-name" style="width:150px" placeholder="Nama racikan (puyer/kapsul)" value="${data?.compound_name || ''}">
                <input type="number" class="form-control form-control-sm compound-packets" style="width:120px" placeholder="Jumlah bungkus" value="${data?.total_packets || ''}" min="1">
                <input type="text" class="form-control form-control-sm compound-instruction" style="width:220px" placeholder="Aturan pakai racikan" value="${data?.instruction || ''}">
            </div>
            <table class="table table-sm table-borderless mb-0" style="width:auto">
                <thead><tr><th>Bahan</th><th>Per Bungkus</th><th>Butuh</th><th>Aksi</th></tr></thead>
                <tbody class="ingredients-body">
                    ${data?.ingredients?.length ? data.ingredients.map((ing, j) => buildIngredientRow(index, j, ing)).join('') : ''}
                </tbody>
            </table>
            <button type="button" class="btn btn-sm btn-outline-primary add-ingredient mt-1">+ Bahan</button>
        `;

        const tr = document.createElement('tr');
        tr.className = 'compound-row';
        tr.innerHTML = `<td colspan="5" class="bg-light p-2"></td>`;
        tr.querySelector('td').appendChild(div);
        return tr;
    }

    function buildIngredientRow(itemIndex, ingIndex, data) {
        let options = '<option value="">-- Pilih --</option>';
        medicines.forEach(m => {
            options += `<option value="${m.id}" data-dosage="${m.dosage_per_unit || 0}"${data?.medicine_id == m.id ? ' selected' : ''}>${m.name}</option>`;
        });
        const calcVal = data?.calculated_qty || '';
        const calcText = calcVal ? `Butuh ${calcVal} tablet` : '';

        return `
            <tr>
                <td>
                    <select name="items[${itemIndex}][ingredients][${ingIndex}][medicine_id]" class="form-select form-select-sm ingredient-medicine" style="width:200px" required>${options}</select>
                </td>
                <td>
                    <input type="number" name="items[${itemIndex}][ingredients][${ingIndex}][qty_per_packet]" class="form-control form-control-sm ingredient-qty" style="width:100px" step="0.01" value="${data?.qty_per_packet || ''}" required>
                    <select name="items[${itemIndex}][ingredients][${ingIndex}][unit]" class="form-select form-select-sm d-inline" style="width:70px">
                        <option value="mg" ${(data?.unit || 'mg') == 'mg' ? 'selected' : ''}>mg</option>
                        <option value="g" ${data?.unit == 'g' ? 'selected' : ''}>g</option>
                        <option value="ml" ${data?.unit == 'ml' ? 'selected' : ''}>ml</option>
                        <option value="tbl" ${data?.unit == 'tbl' ? 'selected' : ''}>tbl</option>
                        <option value="kap" ${data?.unit == 'kap' ? 'selected' : ''}>kap</option>
                    </select>
                </td>
                <td>
                    <input type="hidden" name="items[${itemIndex}][ingredients][${ingIndex}][calculated_qty]" class="ingredient-calc-qty" value="${calcVal}">
                    <span class="ingredient-calc-display small text-primary fw-bold">${calcText}</span>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-ingredient">X</button>
                </td>
            </tr>
        `;
    }

    // ——— Live auto-calc on input changes ———
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('ingredient-qty') || e.target.classList.contains('compound-packets')) {
            const compoundRow = e.target.closest('.compound-row');
            if (compoundRow) recalcCompound(compoundRow);
        }
    });

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('ingredient-medicine')) {
            const compoundRow = e.target.closest('.compound-row');
            if (compoundRow) recalcCompound(compoundRow);
        }
    });

    // ——— Sync compound fields to hidden inputs on submit ———
    document.getElementById('prescriptionForm')?.addEventListener('submit', function() {
        document.querySelectorAll('.compound-row').forEach(row => {
            const tbody = row.closest('tbody');
            const itemRows = Array.from(tbody.querySelectorAll('tr:not(.compound-row)'));
            const prevRow = row.previousElementSibling;
            const index = itemRows.indexOf(prevRow);
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
                        if (oldName) {
                            el.setAttribute('name', oldName.replace(/items\[\d+\]/g, name).replace(/ingredients\[\d+\]/g, `ingredients[${j}]`));
                        }
                    });
                });
            });
        });
    });

    // ——— Add / remove ingredients & items ———
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-ingredient')) {
            const body = e.target.closest('.ms-4').querySelector('.ingredients-body');
            const compoundRow = e.target.closest('.compound-row');
            const tbody = compoundRow.closest('tbody');
            const itemRows = Array.from(tbody.querySelectorAll('tr:not(.compound-row)'));
            const index = itemRows.indexOf(compoundRow.previousElementSibling);
            const ingIndex = body.querySelectorAll('tr').length;

            const tr = document.createElement('tr');
            tr.innerHTML = buildIngredientRow(index, ingIndex, null);
            body.appendChild(tr);
        }

        if (e.target.classList.contains('remove-ingredient')) {
            e.target.closest('tr')?.remove();
        }

        if (e.target.classList.contains('remove-item')) {
            const tr = e.target.closest('tr');
            const tbody = tr.closest('tbody');
            let next = tr.nextElementSibling;
            if (next && next.classList.contains('compound-row')) {
                next.remove();
            }
            tr.remove();
        }
    });

    // Trigger initial copy button state
    document.getElementById('medicalRecordSelect')?.dispatchEvent(new Event('change'));
</script>
@endpush
