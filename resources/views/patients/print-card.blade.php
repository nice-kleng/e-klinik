<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Pasien - {{ $patient->name }}</title>
    <style>
        @page { margin: 0; size: 85.6mm 54mm; }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            width: 85.6mm;
            height: 54mm;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .card {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            display: flex;
            position: relative;
            overflow: hidden;
        }
        .card::before {
            content: '';
            position: absolute;
            top: -30px;
            right: -30px;
            width: 120px;
            height: 120px;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
        }
        .card::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: -20px;
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
        }
        .left {
            width: 62%;
            padding: 8px 10px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            z-index: 1;
        }
        .right {
            width: 38%;
            padding: 8px 10px 8px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 1;
        }
        .clinic-name {
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .clinic-sub {
            color: rgba(255,255,255,0.8);
            font-size: 8px;
        }
        .label {
            color: rgba(255,255,255,0.7);
            font-size: 6.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .value {
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            line-height: 1.3;
        }
        .value-sm {
            font-size: 9px;
        }
        .rm-number {
            font-size: 18px;
            font-weight: 900;
            color: #fff;
            letter-spacing: 1px;
        }
        .rm-label {
            color: rgba(255,255,255,0.7);
            font-size: 6px;
            text-transform: uppercase;
        }
        .qr-container {
            background: #fff;
            padding: 4px;
            border-radius: 4px;
            display: inline-flex;
        }
        .qr-container svg {
            width: 60px;
            height: 60px;
        }
        .divider {
            border: none;
            border-top: 1px dashed rgba(255,255,255,0.3);
            margin: 3px 0;
        }
        .info-row {
            display: flex;
            gap: 8px;
        }
        .info-row .item {
            flex: 1;
        }
        .bpjs-badge {
            background: rgba(255,255,255,0.2);
            color: #fff;
            font-size: 7px;
            padding: 1px 6px;
            border-radius: 3px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="left">
            <div>
                <div class="clinic-name">E-KLINIK</div>
                <div class="clinic-sub">Sehat Bersama Kami</div>
            </div>

            <div>
                <div class="rm-label">NO. REKAM MEDIS</div>
                <div class="rm-number">{{ $patient->no_rm }}</div>
            </div>

            <div>
                <div class="value">{{ $patient->name }}</div>
                <div class="info-row">
                    <div class="item">
                        <div class="label">NIK</div>
                        <div class="value value-sm">{{ $patient->nik ?? '-' }}</div>
                    </div>
                    <div class="item">
                        <div class="label">Tgl Lahir</div>
                        <div class="value value-sm">{{ $patient->birth_date?->format('d/m/Y') ?? '-' }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="item">
                        <div class="label">Jenis Kelamin</div>
                        <div class="value value-sm">{{ $patient->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                    </div>
                    <div class="item">
                        <div class="label">Gol. Darah</div>
                        <div class="value value-sm">{{ $patient->blood_type ?? '-' }}</div>
                    </div>
                </div>
                @if($patient->insurance_type === 'BPJS')
                <div class="mt-1">
                    <span class="bpjs-badge">BPJS: {{ $patient->insurance_number }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="right">
            <div class="qr-container">
                {!! QrCode::size(60)->generate($patient->no_rm) !!}
            </div>
            <div style="color:rgba(255,255,255,0.6);font-size:5.5px;margin-top:3px;text-align:center;">
                Scan untuk verifikasi
            </div>
            <div style="color:rgba(255,255,255,0.8);font-size:6px;margin-top:2px;text-align:center;">
                {{ $patient->phone ?? '' }}
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>