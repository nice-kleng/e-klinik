<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekam Medis</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin: 0; }
        .header p { margin: 2px 0; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        td, th { padding: 4px 8px; text-align: left; }
        .label { font-weight: bold; width: 120px; }
        .section { margin-bottom: 15px; }
        .section h3 { background: #f0f0f0; padding: 5px 8px; font-size: 13px; margin: 0 0 8px 0; }
        .line { border-top: 1px solid #333; margin: 10px 0; }
        .footer { text-align: center; margin-top: 30px; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>REKAM MEDIS</h1>
        <p>{{ $record->polyclinic->name ?? 'Klinik' }}</p>
        <p>{{ date('d/m/Y H:i', strtotime($record->visit_date)) }}</p>
    </div>

    <div class="section">
        <h3>Data Pasien</h3>
        <table>
            <tr><td class="label">No. RM</td><td>: {{ $record->patient->no_rm ?? '-' }}</td></tr>
            <tr><td class="label">Nama</td><td>: {{ $record->patient->name ?? '-' }}</td></tr>
            <tr><td class="label">Tgl Lahir</td><td>: {{ $record->patient->birth_date ? date('d/m/Y', strtotime($record->patient->birth_date)) : '-' }}</td></tr>
            <tr><td class="label">Jenis Kelamin</td><td>: {{ $record->patient->gender ?? '-' }}</td></tr>
            <tr><td class="label">Dokter</td><td>: {{ $record->doctor->name ?? '-' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h3>Subjective (S)</h3>
        <p>{{ $record->subjective_complaint ?? '-' }}</p>
    </div>

    <div class="section">
        <h3>Objective (O)</h3>
        <p>{{ $record->objective_finding ?? $record->physical_exam ?? '-' }}</p>
    </div>

    <div class="section">
        <h3>Assessment (A)</h3>
        <p><strong>Diagnosa:</strong> {{ $record->diagnosis_primary ?? '-' }}</p>
        @if($record->diagnosis_secondary)
            <p><strong>Diagnosa Sekunder:</strong>
                @foreach((array) $record->diagnosis_secondary as $d)
                    {{ is_array($d) ? ($d['icd10_name'] ?? $d['name'] ?? json_encode($d)) : $d }},
                @endforeach
            </p>
        @endif
    </div>

    <div class="section">
        <h3>Plan (P)</h3>
        <p>{{ $record->plan ?? $record->notes ?? '-' }}</p>
    </div>

    <div class="line"></div>

    <div class="footer">
        <p>Dicetak pada {{ date('d/m/Y H:i') }}</p>
        <p>Dokumen ini sah dan ditandatangani secara elektronik</p>
    </div>
</body>
</html>
