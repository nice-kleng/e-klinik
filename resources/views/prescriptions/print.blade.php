<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Resep - {{ $prescription->prescription_number }}</title>
    <style>
        body { background: #fff; font-size: 14px; padding: 2rem; font-family: 'Times New Roman', Times, serif; }
        .header { text-align: center; margin-bottom: 2rem; border-bottom: 2px solid #333; padding-bottom: 1rem; }
        .header h3 { margin-bottom: 0.25rem; text-transform: uppercase; }
        .prescription-info { margin-bottom: 1.5rem; width: 100%; }
        .prescription-info td { padding: 0.25rem 0.5rem; vertical-align: top; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        .items-table th, .items-table td { border: 1px solid #333; padding: 0.5rem; vertical-align: top; }
        .items-table th { background: #f0f0f0; text-align: left; }
        .ingredient-row { font-size: 12px; color: #555; margin-top: 2px; padding-left: 8px; border-left: 2px solid #ccc; }
        .compound-label { font-size: 11px; color: #0d6efd; font-style: italic; }
        .footer { margin-top: 3rem; display: flex; justify-content: flex-end; }
        .footer .signature { text-align: center; width: 200px; }
        .footer .signature .line { margin-top: 3rem; border-top: 1px solid #000; padding-top: 0.25rem; }
        .badge-custom { display: inline-block; padding: 0 6px; font-size: 11px; background: #e9ecef; border-radius: 3px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0.5in; }
        }
    </style>
</head>
<body>
    <div class="no-print mb-3">
        <button class="btn btn-primary" onclick="window.print()">Cetak / Print</button>
        <button class="btn btn-secondary" onclick="window.close()">Tutup</button>
    </div>

    <div class="header">
        <h3>{{ config('app.name', 'e-Klinik') }}</h3>
        <p class="mb-0"><strong>RESEP OBAT</strong></p>
        <p class="mb-0" style="font-size:12px;color:#666;">*Salinan Resep</p>
    </div>

    <table class="prescription-info">
        <tr>
            <td width="100"><strong>No. Resep</strong></td>
            <td>: {{ $prescription->prescription_number }}</td>
        </tr>
        <tr>
            <td><strong>Tanggal</strong></td>
            <td>: {{ $prescription->prescription_date?->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td><strong>Pasien</strong></td>
            <td>: {{ $prescription->patient->name ?? '-' }} ({{ $prescription->patient->no_rm ?? '-' }})</td>
        </tr>
        <tr>
            <td><strong>Dokter</strong></td>
            <td>: {{ $prescription->doctor->name ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Poli</strong></td>
            <td>: {{ $prescription->medicalRecord?->registration?->polyclinic?->name ?? '-' }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
                            <tr>
                                <th width="30">No</th>
                                <th>Nama Obat</th>
                                <th width="80">Jumlah</th>
                                <th>Aturan Pakai</th>
                                <th width="90">Subtotal</th>
                            </tr>
        </thead>
        <tbody>
            @forelse($prescription->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        {{ $item->medicine->name ?? '-' }}
                        @if($item->is_compound)
                            <div class="compound-label">Racikan: {{ $item->compound_name ?? 'Puyer' }}</div>
                            @if($item->ingredients->isNotEmpty())
                                <div class="ingredient-row">
                                    @foreach($item->ingredients as $ing)
                                        <div>{{ $ing->medicine->name ?? '-' }} {{ $ing->qty_per_packet }}{{ $ing->unit }} × {{ $item->total_packets }} = {{ $ing->qty_per_packet * $item->total_packets }}{{ $ing->unit }}
                                            @if($ing->calculated_qty)
                                                <span class="badge-custom">butuh {{ $ing->calculated_qty }} tablet</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </td>
                    <td class="text-center">
                        {{ $item->is_compound ? $item->total_packets . ' bks' : $item->quantity . ' ' . ($item->unit ?? $item->medicine->unit ?? '') }}
                    </td>
                    <td>{{ is_array($item->dosage) ? json_encode($item->dosage) : $item->dosage ?? '-' }}
                        @if($item->is_compound && $item->instruction)
                            <br><small>{{ $item->instruction }}</small>
                        @endif
                    </td>
                    <td class="text-end">Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="color:#999;">Tidak ada item</td>
                </tr>
            @endforelse
        </tbody>
        @if($prescription->items->isNotEmpty())
        <tfoot>
            <tr>
                <td colspan="4" class="text-end fw-bold">Total:</td>
                <td class="text-end fw-bold">Rp {{ number_format($prescription->items->sum('subtotal'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    @if($prescription->notes)
        <div class="mt-3">
            <strong>Catatan:</strong>
            <p>{{ $prescription->notes }}</p>
        </div>
    @endif

    <div class="footer">
        <div class="signature">
            <p>Dokter,</p>
            <div class="line">{{ $prescription->doctor->name ?? '' }}</div>
            @if($prescription->doctor->sip_number)
                <small>SIP. {{ $prescription->doctor->sip_number }}</small>
            @endif
        </div>
    </div>

    <script>
        window.onload = function() { setTimeout(function() { window.print(); }, 500); };
    </script>
</body>
</html>
