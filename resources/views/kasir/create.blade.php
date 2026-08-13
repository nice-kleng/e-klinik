@extends('layouts.volt')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="fas fa-plus-circle me-1"></i>Buat Invoice</h4>
        <a href="{{ route('kasir.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>
    @include('components.alert')
    @if(!isset($registration))
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><h6 class="mb-0">Pilih Pendaftaran</h6></div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr><th>#Reg</th><th>Pasien</th><th>Poli</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($registrations as $reg)
                    <tr>
                        <td>{{ $reg->registration_number }}</td>
                        <td>{{ $reg->patient->name ?? '-' }}</td>
                        <td>{{ $reg->polyclinic->name ?? '-' }}</td>
                        <td>
                            @php
                                $statusLabel = match($reg->service_status) {
                                    'in_consultation' => 'Konsultasi',
                                    'pharmacy' => 'Farmasi',
                                    'cashier' => 'Kasir',
                                    default => $reg->service_status,
                                };
                                $statusColor = match($reg->service_status) {
                                    'in_consultation' => 'secondary',
                                    'pharmacy' => 'warning',
                                    'cashier' => 'danger',
                                    default => 'info',
                                };
                            @endphp
                            <span class="badge bg-{{ $statusColor }}">{{ $statusLabel }}</span>
                        </td>
                        <td>
                            <a href="{{ route('kasir.create', ['registration_id' => $reg->id]) }}" class="btn btn-sm btn-primary">Pilih</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada pasien yang perlu dibuatkan invoice hari ini</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @else
    <form method="POST" action="{{ route('kasir.store') }}" id="form-invoice">
        @csrf
        <input type="hidden" name="registration_id" value="{{ $registration->id }}">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0">Informasi Pasien</h6></div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <tr><td class="text-muted">Nama</td><td><strong>{{ $patient->name }}</strong></td></tr>
                            <tr><td class="text-muted">RM</td><td>{{ $patient->medical_record_number }}</td></tr>
                            <tr><td class="text-muted">Poli</td><td>{{ $registration->polyclinic->name ?? '-' }}</td></tr>
                            <tr><td class="text-muted">Dokter</td><td>{{ $registration->doctor->name ?? '-' }}</td></tr>
                            <tr><td class="text-muted">Registrasi</td><td>{{ $registration->registration_number }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Item Tagihan</h6>
                        <span class="badge bg-primary fs-6" id="total-display">Rp 0</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="px-3 pt-3">
                            <h6 class="text-muted"><i class="fas fa-stethoscope me-1"></i>Pelayanan</h6>
                        </div>
                        <table class="table table-sm mb-0">
                            <thead><tr><th><input type="checkbox" class="consultation-all" checked></th><th>Item</th><th>Jumlah</th><th>Harga</th><th>Subtotal</th></tr></thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="item-check consultation" checked
                                            data-type="consultation"
                                            data-id="{{ $registration->id }}"
                                            data-desc="Biaya Konsultasi"
                                            data-qty="1"
                                            data-price="{{ $consultationFee }}">
                                    </td>
                                    <td>Biaya Konsultasi</td>
                                    <td>1</td>
                                    <td class="text-end">
                                        <div class="input-group input-group-sm justify-content-end">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control form-control-sm text-end consultation-price"
                                                value="{{ $consultationFee }}" min="0" style="max-width:130px">
                                        </div>
                                    </td>
                                    <td class="text-end consultation-subtotal">Rp {{ number_format($consultationFee, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>

                        @if($prescriptions->count() > 0)
                        <div class="px-3 pt-3">
                            <h6 class="text-muted"><i class="fas fa-prescription me-1"></i>Resep</h6>
                        </div>
                        <table class="table table-sm mb-0">
                            <thead><tr><th><input type="checkbox" class="check-group" data-group="prescription"></th><th>Obat</th><th>Jumlah</th><th>Harga</th><th>Subtotal</th></tr></thead>
                            <tbody>
                                @foreach($prescriptions as $prescription)
                                    @foreach($prescription->items as $item)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="item-check prescription" name="items[{{ $loop->parent->index }}_{{ $loop->index }}][checked]"
                                                data-type="prescription_item"
                                                data-id="{{ $item->id }}"
                                                data-desc="{{ $item->medicine->name ?? $item->description }} ({{ $item->dosage_text ?? '' }})"
                                                data-qty="{{ $item->quantity }}"
                                                data-price="{{ $item->selling_price ?? $item->unit_price ?? 0 }}">
                                        </td>
                                        <td>{{ $item->medicine->name ?? $item->description }}</td>
                                        <td>{{ $item->quantity }} {{ $item->medicine->unit ?? '' }}</td>
                                        <td class="text-end">Rp {{ number_format($item->selling_price ?? $item->unit_price ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format(($item->selling_price ?? $item->unit_price ?? 0) * $item->quantity, 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                        @endif

                        @if($procedures->count() > 0)
                        <div class="px-3 pt-3">
                            <h6 class="text-muted"><i class="fas fa-procedures me-1"></i>Tindakan</h6>
                        </div>
                        <table class="table table-sm mb-0">
                            <thead><tr><th><input type="checkbox" class="check-group" data-group="procedure"></th><th>Tindakan</th><th>Jumlah</th><th>Harga</th><th>Subtotal</th></tr></thead>
                            <tbody>
                                @foreach($procedures as $proc)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="item-check procedure"
                                            data-type="procedure"
                                            data-id="{{ $proc->id }}"
                                            data-desc="{{ $proc->procedure_name ?? $proc->description ?? 'Tindakan #'.$proc->id }}"
                                            data-qty="1"
                                            data-price="{{ $proc->fee ?? 0 }}">
                                    </td>
                                    <td>{{ $proc->procedure_name ?? $proc->description ?? 'Tindakan #'.$proc->id }}</td>
                                    <td>1</td>
                                    <td class="text-end">Rp {{ number_format($proc->fee ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($proc->fee ?? 0, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif

                        @if($labRequests->count() > 0)
                        <div class="px-3 pt-3">
                            <h6 class="text-muted"><i class="fas fa-flask me-1"></i>Laboratorium</h6>
                        </div>
                        <table class="table table-sm mb-0">
                            <thead><tr><th><input type="checkbox" class="check-group" data-group="lab"></th><th>Pemeriksaan</th><th>Jumlah</th><th>Harga</th><th>Subtotal</th></tr></thead>
                            <tbody>
                                @foreach($labRequests as $req)
                                    @foreach($req->items as $labItem)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="item-check lab"
                                                data-type="lab_item"
                                                data-id="{{ $labItem->id }}"
                                                data-desc="{{ $labItem->test->name ?? 'Lab #'.$labItem->id }}"
                                                data-qty="1"
                                                data-price="{{ $labItem->fee ?? $labItem->price ?? 0 }}">
                                        </td>
                                        <td>{{ $labItem->test->name ?? 'Lab #'.$labItem->id }}</td>
                                        <td>1</td>
                                        <td class="text-end">Rp {{ number_format($labItem->fee ?? $labItem->price ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($labItem->fee ?? $labItem->price ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                        <span>Total: <strong id="total-footer">Rp 0</strong></span>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-file-invoice me-1"></i>Buat Invoice
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @endif
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('form-invoice');
    if (!form) return;

    var totalDisplay = document.getElementById('total-display');
    var totalFooter = document.getElementById('total-footer');

    function updateTotal() {
        var total = 0;
        document.querySelectorAll('.item-check:checked').forEach(function(cb) {
            var price = parseFloat(cb.dataset.price) || 0;
            var qty = parseFloat(cb.dataset.qty) || 1;
            total += price * qty;
        });
        var formatted = 'Rp ' + total.toLocaleString('id-ID');
        if (totalDisplay) totalDisplay.textContent = formatted;
        if (totalFooter) totalFooter.textContent = formatted;
    }

    var consultCheck = document.querySelector('.item-check.consultation');
    var consultPriceInput = document.querySelector('.consultation-price');
    var consultSubtotal = document.querySelector('.consultation-subtotal');

    function syncConsultationPrice() {
        if (!consultCheck || !consultPriceInput) return;
        var price = parseFloat(consultPriceInput.value) || 0;
        consultCheck.dataset.price = price;
        if (consultSubtotal) {
            consultSubtotal.textContent = 'Rp ' + price.toLocaleString('id-ID');
        }
        updateTotal();
    }

    if (consultPriceInput) {
        consultPriceInput.addEventListener('input', syncConsultationPrice);
    }

    document.querySelectorAll('.check-group').forEach(function(groupCb) {
        groupCb.addEventListener('change', function() {
            var group = this.dataset.group;
            document.querySelectorAll('.item-check.' + group).forEach(function(cb) {
                cb.checked = groupCb.checked;
            });
            updateTotal();
        });
    });

    var consultAll = document.querySelector('.consultation-all');
    if (consultAll) {
        consultAll.addEventListener('change', function() {
            if (consultCheck) consultCheck.checked = consultAll.checked;
            updateTotal();
        });
    }

    document.querySelectorAll('.item-check').forEach(function(cb) {
        cb.addEventListener('change', updateTotal);
    });

    form.addEventListener('submit', function(e) {
        var checked = document.querySelectorAll('.item-check:checked');
        if (checked.length === 0) {
            e.preventDefault();
            alert('Pilih minimal satu item tagihan');
            return;
        }
        checked.forEach(function(cb, i) {
            var itemableType = cb.dataset.type === 'prescription_item' ? 'App\\Models\\PrescriptionItem'
                : cb.dataset.type === 'procedure' ? 'App\\Models\\MedicalRecordProcedure'
                : cb.dataset.type === 'lab_item' ? 'App\\Models\\LabRequestItem'
                : 'App\\Models\\Registration';
            var hiddenHtml = '';
            hiddenHtml += '<input type="hidden" name="items[' + i + '][item_type]" value="' + cb.dataset.type + '">';
            hiddenHtml += '<input type="hidden" name="items[' + i + '][itemable_type]" value="' + itemableType + '">';
            hiddenHtml += '<input type="hidden" name="items[' + i + '][itemable_id]" value="' + cb.dataset.id + '">';
            hiddenHtml += '<input type="hidden" name="items[' + i + '][description]" value="' + cb.dataset.desc + '">';
            hiddenHtml += '<input type="hidden" name="items[' + i + '][quantity]" value="' + cb.dataset.qty + '">';
            hiddenHtml += '<input type="hidden" name="items[' + i + '][unit_price]" value="' + cb.dataset.price + '">';
            form.insertAdjacentHTML('beforeend', hiddenHtml);
        });
    });

    syncConsultationPrice();
});
</script>
@endpush