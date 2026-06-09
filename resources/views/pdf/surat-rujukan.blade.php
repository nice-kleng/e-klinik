<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Rujukan</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { font-size: 16px; margin: 0; }
        .content { margin-bottom: 20px; }
        table { width: 100%; }
        td { padding: 4px 8px; }
        .label { width: 120px; font-weight: bold; }
        .body-text { margin: 15px 0; padding: 10px; border: 1px solid #ccc; }
        .footer { margin-top: 40px; }
        .signature { width: 200px; text-align: center; float: right; }
        .clear { clear: both; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SURAT RUJUKAN</h1>
        <p>Rujukan Internal / Antar Faskes</p>
    </div>

    <div class="content">
        <table>
            <tr><td class="label">No. Rujukan</td><td>: {{ $referral->no_rujukan ?? '-' }}</td></tr>
            <tr><td class="label">Nama Pasien</td><td>: {{ $referral->patient->name ?? '-' }}</td></tr>
            <tr><td class="label">No. RM</td><td>: {{ $referral->patient->no_rm ?? '-' }}</td></tr>
            <tr><td class="label">Tgl Lahir</td><td>: {{ $referral->patient->birth_date ? date('d/m/Y', strtotime($referral->patient->birth_date)) : '-' }}</td></tr>
            <tr><td class="label">Diagnosa</td><td>: {{ $referral->diagnose ?? '-' }}</td></tr>
            <tr><td class="label">Faskes Tujuan</td><td>: {{ $referral->ppk_dirujuk ?? '-' }}</td></tr>
            <tr><td class="label">Tgl Rujukan</td><td>: {{ $referral->tgl_kunjungan ? date('d/m/Y', strtotime($referral->tgl_kunjungan)) : '-' }}</td></tr>
        </table>
    </div>

    <div class="footer">
        <div class="signature">
            <p>Dokter Perujuk,</p>
            <br><br><br>
            <p>( {{ $referral->medicalRecord->doctor->name ?? 'Dokter' }} )</p>
        </div>
        <div class="clear"></div>
    </div>
</body>
</html>
