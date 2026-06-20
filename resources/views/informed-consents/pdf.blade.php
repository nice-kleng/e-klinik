<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informed Consent #{{ $informedConsent->id }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.6; color: #000; margin: 40px; }
        h1 { text-align: center; font-size: 16pt; margin-bottom: 5px; text-transform: uppercase; }
        h2 { font-size: 14pt; margin: 20px 0 10px; border-bottom: 1px solid #333; padding-bottom: 5px; }
        h3 { font-size: 12pt; margin: 15px 0 5px; }
        .header-center { text-align: center; margin-bottom: 30px; }
        .header-center h1 { margin-bottom: 2px; }
        .header-center p { margin: 2px 0; font-size: 11pt; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .data-table td { padding: 4px 8px; vertical-align: top; }
        .data-table td:first-child { width: 180px; font-weight: bold; }
        .signature-section { margin-top: 40px; }
        .signature-grid { width: 100%; }
        .signature-grid td { width: 33%; vertical-align: top; padding: 10px; text-align: center; }
        .signature-box { border-top: 1px solid #000; margin-top: 60px; padding-top: 5px; }
        .qr-code { text-align: center; margin: 20px 0; }
        .qr-code img { width: 80px; height: 80px; }
        .footer-text { text-align: center; font-size: 9pt; color: #666; margin-top: 20px; border-top: 1px solid #ccc; padding-top: 8px; }
        .ttd-text { font-size: 11pt; margin-top: 5px; }
        .text-muted { color: #666; }
        .small { font-size: 9pt; }
        .hash { font-size: 8pt; word-break: break-all; color: #999; margin-top: 5px; }
        .badge { display: inline-block; padding: 2px 8px; font-size: 9pt; border: 1px solid #333; }
    </style>
</head>
<body>
    <div class="header-center">
        <h1>INFORMED CONSENT</h1>
        <p><strong>{{ config('app.name') }}</strong></p>
        <p>{{ config('app.address') ?? 'Jl. Contoh No. 123, Kota' }}</p>
        <hr style="margin: 15px 0;">
    </div>

    <table class="data-table">
        <tr><td>No. Registrasi</td><td>: {{ $informedConsent->registration_id ?? '-' }}</td></tr>
        <tr><td>Nama Pasien</td><td>: <strong>{{ $informedConsent->patient->name ?? '-' }}</strong></td></tr>
        <tr><td>No. RM</td><td>: {{ $informedConsent->patient->no_rm ?? '-' }}</td></tr>
        @if($informedConsent->patient->nik)
        <tr><td>NIK</td><td>: {{ $informedConsent->patient->nik }}</td></tr>
        @endif
        @if($informedConsent->patient->birth_date)
        <tr><td>Tgl. Lahir / Umur</td><td>: {{ $informedConsent->patient->birth_date->format('d/m/Y') }}</td></tr>
        @endif
    </table>

    <h2>PERSETUJUAN TINDAKAN MEDIS</h2>

    <p>Saya yang bertanda tangan di bawah ini :</p>
    <table class="data-table">
        <tr><td>Nama</td><td>: <strong>{{ $informedConsent->patient_name ?? $informedConsent->patient->name ?? '-' }}</strong></td></tr>
        <tr><td>Status</td><td>: Pasien / Wali</td></tr>
    </table>

    <p>Setelah mendapat penjelasan dari dokter, dengan ini menyatakan <strong>SETUJU</strong> untuk dilakukan tindakan medis berupa:</p>

    <table class="data-table">
        <tr><td>Jenis Tindakan</td><td>: <strong>{{ $informedConsent->procedure_name ?? ($informedConsent->procedureIcd9?->code . ' — ' . $informedConsent->procedureIcd9?->name ?? '-') }}</strong></td></tr>
        <tr><td>Tipe Persetujuan</td><td>: {{ $informedConsent->consent_type }}</td></tr>
        @if($informedConsent->diagnosis)
        <tr><td>Diagnosis</td><td>: {{ $informedConsent->diagnosis }}</td></tr>
        @endif
        @if($informedConsent->purpose)
        <tr><td>Tujuan Tindakan</td><td>: {{ $informedConsent->purpose }}</td></tr>
        @endif
        @if($informedConsent->risks)
        <tr><td>Risiko</td><td>: {{ $informedConsent->risks }}</td></tr>
        @endif
        @if($informedConsent->benefits)
        <tr><td>Manfaat</td><td>: {{ $informedConsent->benefits }}</td></tr>
        @endif
        @if($informedConsent->alternatives)
        <tr><td>Alternatif</td><td>: {{ $informedConsent->alternatives }}</td></tr>
        @endif
        @if($informedConsent->doctor_recommendation)
        <tr><td>Rekomendasi Dokter</td><td>: {{ $informedConsent->doctor_recommendation }}</td></tr>
        @endif
    </table>

    <p>Demikian persetujuan ini saya buat dengan kesadaran penuh tanpa paksaan dari pihak manapun.</p>

    <div class="signature-section">
        <table class="signature-grid">
            <tr>
                <td>
                    <p><strong>Yang Menyetujui,</strong></p>
                    <div class="signature-box">
                        @if($informedConsent->patient_signed_at)
                            <p class="ttd-text">{{ $informedConsent->patient_name }}</p>
                        @endif
                        <p class="small text-muted">(Pasien / Wali)</p>
                        <p class="small">Tgl: {{ $informedConsent->patient_signed_at?->format('d/m/Y H:i') ?? '-' }}</p>
                    </div>
                </td>
                <td>
                    <p><strong>Saksi,</strong></p>
                    <div class="signature-box">
                        @if($informedConsent->witness_name)
                            <p class="ttd-text">{{ $informedConsent->witness_name }}</p>
                        @endif
                        <p class="small text-muted">(Saksi)</p>
                    </div>
                </td>
                <td>
                    <p><strong>Dokter,</strong></p>
                    <div class="signature-box">
                        @if($informedConsent->signer)
                            <p class="ttd-text">{{ $informedConsent->signer->name }}</p>
                        @endif
                        <p class="small text-muted">(Dokter)</p>
                        <p class="small">Tgl: {{ $informedConsent->signed_at?->format('d/m/Y H:i') ?? '-' }}</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    @if($qrCodeSvg)
    <div class="qr-code">
        {!! $qrCodeSvg !!}
        <p class="small text-muted">Scan untuk verifikasi</p>
    </div>
    @endif

    <div class="footer-text">
        <p>Dokumen ini ditandatangani secara elektronik pada {{ $informedConsent->signed_at?->format('d/m/Y H:i:s') ?? '-' }}</p>
        @if($informedConsent->signature_hash)
            <p class="hash">SHA-256: {{ $informedConsent->signature_hash }}</p>
        @endif
    </div>
</body>
</html>
