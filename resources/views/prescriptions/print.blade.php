<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Resep - {{ $prescription->prescription_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #fff; font-size: 14px; padding: 2rem; }
        .header { text-align: center; margin-bottom: 2rem; border-bottom: 2px solid #333; padding-bottom: 1rem; }
        .header h3 { margin-bottom: 0.25rem; }
        .prescription-info { margin-bottom: 1.5rem; }
        .prescription-info td { padding: 0.25rem 0.5rem; }
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table th, .items-table td { border: 1px solid #333; padding: 0.5rem; }
        .items-table th { background: #f0f0f0; }
        .footer { margin-top: 3rem; display: flex; justify-content: space-between; }
        .footer .signature { text-align: center; width: 200px; }
        .footer .signature .line { margin-top: 3rem; border-top: 1px solid #000; padding-top: 0.25rem; }
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
        <p class="mb-0">RESEP OBAT</p>
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
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Obat</th>
                <th>Jumlah</th>
                <th>Aturan Pakai</th>
            </tr>
        </thead>
        <tbody>
            @foreach($prescription->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->medicine->name ?? '-' }}</td>
                    <td class="text-center">{{ $item->quantity }} {{ $item->unit ?? $item->medicine->unit ?? '' }}</td>
                    <td>{{ is_array($item->dosage) ? json_encode($item->dosage) : $item->dosage ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($prescription->notes)
        <div class="mt-3">
            <strong>Catatan:</strong>
            <p>{{ $prescription->notes }}</p>
        </div>
    @endif

    <div class="footer">
        <div></div>
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
