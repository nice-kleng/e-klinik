<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.6; margin: 60px; }
        .kop { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop h1 { font-size: 18pt; margin: 0; text-transform: uppercase; }
        .kop p { margin: 2px 0; font-size: 11pt; }
        .title { text-align: center; font-size: 14pt; font-weight: bold; text-decoration: underline; margin: 20px 0; }
        .content { text-align: justify; }
        .ttd { margin-top: 40px; text-align: right; }
        table { width: 100%; }
        td { padding: 2px 5px; vertical-align: top; }
        .label { width: 150px; }
    </style>
</head>
<body>
    <div class="kop">
        <h1>KLINIK SEHAT</h1>
        <p>Jl. Kesehatan No. 1, Kota</p>
        <p>Telp: (021) 123456 | Email: info@kliniksehat.com</p>
    </div>

    <div class="title">SURAT KETERANGAN MEDIS</div>
    <p style="text-align:center">Nomor: SKM/{{ $registration->registration_number }}</p>

    <div class="content">
        <p>Yang bertanda tangan di bawah ini, Dokter yang memeriksa pasien di Klinik Sehat, menerangkan bahwa:</p>

        <table>
            <tr><td class="label">Nama</td><td>: {{ $registration->patient->name }}</td></tr>
            <tr><td>NIK</td><td>: {{ $registration->patient->nik ?? '-' }}</td></tr>
            <tr><td>Tempat/Tgl Lahir</td><td>: {{ $registration->patient->birth_place ?? '-' }}, {{ $registration->patient->birth_date?->format('d/m/Y') ?? '-' }}</td></tr>
            <tr><td>Jenis Kelamin</td><td>: {{ $registration->patient->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td></tr>
            <tr><td>Alamat</td><td>: {{ $registration->patient->address ?? '-' }}</td></tr>
        </table>

        <p>Telah diperiksa pada tanggal {{ $registration->registration_date?->format('d/m/Y') }} dengan keluhan dan diagnosis sebagai berikut:</p>

        @if($mr)
            <p><strong>Diagnosis:</strong>
            @foreach($mr->diagnoses as $d)
                {{ $d->icd10Diagnosis->code ?? '' }} ({{ $d->icd10Diagnosis->name ?? '' }})
                @if(!$loop->last), @endif
            @endforeach
            @if($mr->diagnoses->isEmpty() && $mr->diagnosis_primary)
                {{ $mr->diagnosis_primary }}
            @endif
            </p>
        @endif

        <p>Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.</p>
    </div>

    <div class="ttd">
        <p>Kota, {{ now()->format('d F Y') }}</p>
        <p>Dokter Pemeriksa,</p>
        <br><br><br>
        <p><strong>{{ $registration->doctor->name ?? '_________________' }}</strong></p>
        <p><small>SIP. _________________</small></p>
    </div>
</body>
</html>
