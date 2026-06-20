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
        .qr-container { text-align: center; margin: 15px 0; }
        .tte-info { text-align: center; font-size: 9px; color: #444; margin-top: 5px; }
        .tte-info strong { font-size: 10px; }
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
        @if($record->anamnesis)
            <p><strong>Anamnesis:</strong> {{ $record->anamnesis }}</p>
        @endif
    </div>

    <div class="section">
        <h3>Objective (O)</h3>
        @if($record->objective_finding)
            <p><strong>Pemeriksaan Fisik Umum:</strong> {{ $record->objective_finding }}</p>
        @endif
        @if($record->physical_exam)
            <p><strong>Pemeriksaan Fisik Detail:</strong> {{ $record->physical_exam }}</p>
        @endif
        @if(!$record->objective_finding && !$record->physical_exam)
            <p>{{ '-' }}</p>
        @endif
    </div>

    <div class="section">
        <h3>Assessment (A)</h3>
        @if($record->assessment)
            <p><strong>Assessment:</strong> {{ $record->assessment }}</p>
        @endif
        @php
            $primaryDiag = $record->diagnoses?->where('type', 'primary')->first();
        @endphp
        @if($primaryDiag)
            <p><strong>Diagnosa Utama:</strong> {{ $primaryDiag->icd10Diagnosis?->code }} — {{ $primaryDiag->icd10Diagnosis?->name }}</p>
        @elseif($record->diagnosis_primary)
            <p><strong>Diagnosa Utama:</strong> {{ $record->diagnosis_primary }}</p>
        @endif
        @php
            $secondaryDiags = $record->diagnoses?->where('type', 'secondary');
        @endphp
        @if($secondaryDiags?->isNotEmpty())
            <p><strong>Diagnosa Sekunder:</strong>
                @foreach($secondaryDiags as $sd)
                    {{ $sd->icd10Diagnosis?->code ?? '' }},
                @endforeach
            </p>
        @endif
        @if($record->differential_diagnosis)
            <p><strong>Diagnosis Banding:</strong> {{ $record->differential_diagnosis }}</p>
        @endif
    </div>

    <div class="section">
        <h3>Plan (P)</h3>
        <p>{{ $record->plan ?? $record->notes ?? '-' }}</p>
    </div>

    @if($record->signature_hash)
        <div class="qr-container">
            {!! $qrCodeSvg !!}
        </div>
        <div class="tte-info">
            <p><strong>Hash SHA-256:</strong> {{ $record->signature_hash }}</p>
            <p><strong>Ditandatangani oleh:</strong> {{ $record->signer?->name ?? '-' }}</p>
            <p><strong>Waktu TTE:</strong> {{ $record->signed_at?->format('d/m/Y H:i:s') }}</p>
            <p><em>Dokumen ini sah dan ditandatangani secara elektronik. Verifikasi: {{ route('medical-records.verify', $record->signature_hash) }}</em></p>
        </div>
    @endif

    <div class="line"></div>

    <div class="footer">
        <p>Dicetak pada {{ date('d/m/Y H:i') }}</p>
        @if(!$record->signature_hash)
            <p>Dokumen ini belum ditandatangani secara elektronik</p>
        @endif
    </div>
</body>
</html>
