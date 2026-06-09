<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resep Obat</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 16px; margin: 0; }
        .header p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 5px 8px; text-align: left; }
        th { background: #f0f0f0; }
        .info { margin-bottom: 15px; }
        .info td { border: none; padding: 2px 8px; }
        .footer { margin-top: 40px; }
        .signature { width: 200px; text-align: center; margin-top: 50px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>RESEP OBAT</h1>
        <p>No. Resep: {{ $prescription->prescription_number }}</p>
        <p>{{ date('d/m/Y', strtotime($prescription->prescription_date)) }}</p>
    </div>

    <table class="info">
        <tr><td style="width:80px">Nama Pasien</td><td>: {{ $prescription->patient->name ?? '-' }}</td></tr>
        <tr><td>No. RM</td><td>: {{ $prescription->patient->no_rm ?? '-' }}</td></tr>
        <tr><td>Dokter</td><td>: {{ $prescription->doctor->name ?? '-' }}</td></tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width:30px">No</th>
                <th>Nama Obat</th>
                <th>Dosis</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($prescription->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->medicine->name ?? '-' }}</td>
                    <td>{{ $item->dosage ?? $item->unit ?? '-' }}</td>
                    <td>{{ $item->quantity ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Catatan: {{ $prescription->notes ?? '-' }}</p>
        <div class="signature">
            <p>Dokter Penulis Resep,</p>
            <br><br><br>
            <p>{{ $prescription->doctor->name ?? '' }}</p>
            <p>SIP: {{ $prescription->doctor->sip_number ?? '' }}</p>
        </div>
    </div>
</body>
</html>
