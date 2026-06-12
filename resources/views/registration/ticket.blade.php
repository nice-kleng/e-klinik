<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tiket Antrean - {{ $queue->queue_number }}</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            width: 80mm;
            margin: 0;
            padding: 10px;
            font-size: 12px;
        }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .fs-large { font-size: 24px; }
        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .ticket-number {
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>
    <div class="text-center">
        <h3 style="margin:0;">e-KLINIK</h3>
        <small>Tiket Antrean</small>
        <hr>

        <div class="ticket-number">{{ $queue->queue_number }}</div>
        <hr>

        <table style="width:100%; font-size:11px;">
            <tr>
                <td>Nama</td>
                <td>: {{ $queue->registration?->patient?->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>No. RM</td>
                <td>: {{ $queue->registration?->patient?->no_rm ?? '-' }}</td>
            </tr>
            <tr>
                <td>Poli</td>
                <td>: {{ $queue->polyclinic?->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>: {{ now()->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td>Daftar</td>
                <td>: {{ now()->format('H:i:s') }}</td>
            </tr>
        </table>

        <hr>
        <small>Harap menunggu panggilan dari poli</small>
    </div>

    <script>
        window.print();
    </script>
</body>
</html>
