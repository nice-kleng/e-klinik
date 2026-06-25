@php $reg = $queue->registration; $mr = $queue->medicalRecord; @endphp
@if($prescriptions->isNotEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><h6 class="mb-0">Resep</h6></div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr><th>No. Resep</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @foreach($prescriptions as $prescription)
                        <tr>
                            <td>{{ $prescription->prescription_number }}</td>
                            <td>{{ $prescription->prescription_date?->format('d/m/Y') }}</td>
                            <td>
                                @php
                                    $pBadge2 = match($prescription->status) { 'active'=>'bg-success','dispensed'=>'bg-info','cancelled'=>'bg-secondary', default=>'bg-warning' };
                                    $pLabel2 = match($prescription->status) { 'active'=>'Aktif','dispensed'=>'Diberikan','cancelled'=>'Dibatalkan', default=>$prescription->status };
                                @endphp
                                <span class="badge {{ $pBadge2 }}">{{ $pLabel2 }}</span>
                            </td>
                            <td><a href="{{ route('prescriptions.show', $prescription) }}" class="btn btn-sm btn-info">Detail</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="text-center py-5 text-muted">
        <i class="fas fa-prescription fa-3x mb-3 d-block"></i>
        <p>Belum ada resep untuk kunjungan ini.</p>
        @if($mr)
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#prescriptionModal">
            <i class="fas fa-plus-circle me-1"></i>Buat Resep
        </button>
        @endif
    </div>
@endif
