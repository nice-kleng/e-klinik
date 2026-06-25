@php
    $medsList = $medicines ?? \App\Models\Medicine::where('is_active', true)->orderBy('name')->get();
@endphp

<div class="modal fade" id="prescriptionModal" tabindex="-1" aria-labelledby="prescriptionModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="prescriptionModalLabel">
                    <i class="fas fa-prescription me-1"></i>Buat Resep — {{ $patient?->name ?? 'Pasien' }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form method="POST" action="{{ route('prescriptions.store') }}" id="prescriptionForm">
                @csrf
                <input type="hidden" name="medical_record_id" value="{{ $mr->id }}">
                <input type="hidden" name="patient_id" id="patientIdField" value="{{ $patient?->id }}">

                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex gap-2 align-items-center">
                            <span class="text-muted small">
                                {{ $mr->visit_date?->format('d/m/Y') }} &middot;
                                {{ $reg?->doctor?->name ?? $mr->doctor?->name ?? '-' }}
                            </span>
                            <button type="button" class="btn btn-outline-info btn-sm" id="copyLastRx" style="display:none">
                                <i class="fas fa-copy me-1"></i>Salin dari resep sebelumnya
                            </button>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input type="checkbox" class="form-check-input" id="autoCalcToggle" {{ config('pharmacy.auto_calc', true) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="autoCalcToggle">Auto-kalkulasi</label>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Catatan</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                            <h6 class="mb-0">Item Resep</h6>
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
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Simpan Resep
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const rxMedicines = @json($medsList->map(fn($m) => ['id' => $m->id, 'name' => $m->name, 'dosage_per_unit' => $m->dosage_per_unit]));

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
        const med = rxMedicines.find(m => m.id === medId);
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

    // ——— Copy last prescription ———
    document.getElementById('copyLastRx')?.addEventListener('click', function() {
        const patientId = {{ $patient?->id ?? 'null' }};
        if (!patientId) return;

        fetch('{{ url('prescriptions/last') }}/' + patientId)
            .then(r => r.json())
            .then(res => {
                if (!res.success) { alert(res.message); return; }
                if (!confirm('Salin ' + res.data.items.length + ' item dari resep ' + res.data.prescription_number + ' (' + res.data.prescription_date + ')?')) return;

                const tbody = document.querySelector('#itemsTable tbody');
                tbody.innerHTML = '';
                res.data.items.forEach((item, i) => addRxRow(item, i));
            })
            .catch(() => alert('Gagal mengambil data resep sebelumnya'));
    });

    // ——— Auto-show copy button ———
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('copyLastRx');
        const patientId = {{ $patient?->id ?? 'null' }};
        if (patientId) btn.style.display = 'inline-block';
    });

    // ——— Add item row ———
    document.getElementById('addItem')?.addEventListener('click', function() {
        const tbody = document.querySelector('#itemsTable tbody');
        const index = tbody.querySelectorAll('tr:not(.compound-row)').length;
        addRxRow(null, index);
    });

    function addRxRow(data, index) {
        const tbody = document.querySelector('#itemsTable tbody');
        const i = index;

        let options = '<option value="">-- Pilih Obat --</option>';
        rxMedicines.forEach(m => { options += `<option value="${m.id}"${data?.medicine_id == m.id ? ' selected' : ''}>${m.name}</option>`; });

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
            if (nextRow && nextRow.classList.contains('compound-row')) nextRow.remove();

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
        rxMedicines.forEach(m => {
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

    // ——— Live auto-calc ———
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

    // ——— Sync compound fields on submit ———
    document.getElementById('prescriptionForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        // Sync compound fields
        document.querySelectorAll('.compound-row').forEach(row => {
            const tbody = row.closest('tbody');
            const itemRows = Array.from(tbody.querySelectorAll('tr:not(.compound-row)'));
            const prevRow = row.previousElementSibling;
            const index = itemRows.indexOf(prevRow);
            if (index < 0) return;

            const div = row.querySelector('.ms-4');
            const name = `items[${index}]`;
            prevRow.querySelector(`[name="${name}[compound_name]"]`).value = div.querySelector('.compound-name')?.value || '';
            prevRow.querySelector(`[name="${name}[total_packets]"]`).value = div.querySelector('.compound-packets')?.value || '';
            prevRow.querySelector(`[name="${name}[instruction]"]`).value = div.querySelector('.compound-instruction')?.value || '';

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

        const form = this;
        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('prescriptionModal'));
                modal.hide();
                form.reset();
                document.querySelector('#itemsTable tbody').innerHTML = '';

                showToast('success', res.message || 'Resep berhasil ditambahkan');

                // Reload resep tab content
                if (typeof loadResepTab === 'function') loadResepTab();
            } else {
                showToast('danger', res.message || 'Gagal menyimpan resep');
            }
        })
        .catch(err => {
            showToast('danger', 'Terjadi kesalahan server');
            console.error(err);
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
            if (next && next.classList.contains('compound-row')) next.remove();
            tr.remove();
        }
    });
</script>
@endpush
