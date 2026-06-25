<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Courier New', monospace; font-size: 12px; width: 80mm; margin: 0 auto; padding: 10px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-muted { color: #666; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px 2px; }
        .border-top { border-top: 1px dashed #333; }
        .border-bottom { border-bottom: 1px dashed #333; }
        .totals th { padding-top: 6px; }
        hr { border: none; border-top: 1px dashed #333; margin: 8px 0; }
        h2, h4 { margin: 4px 0; }
        .footer { margin-top: 12px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="text-center">
        <h2>E-KLINIK</h2>
        <div class="text-muted">Jl. Raya Sehat No. 1</div>
        <div class="text-muted">Telp: (021) 1234567</div>
        <hr>
        <h4>INVOICE</h4>
        <div>{{ $invoice->invoice_number }}</div>
        <div>{{ $invoice->created_at->format('d/m/Y H:i') }}</div>
    </div>
    <hr>
    <table>
        <tr><td>Pasien</td><td class="text-right"><strong>{{ $invoice->patient->name ?? '-' }}</strong></td></tr>
        <tr><td>Poli</td><td class="text-right">{{ $invoice->polyclinic->name ?? '-' }}</td></tr>
        <tr><td>Dokter</td><td class="text-right">{{ $invoice->doctor->name ?? '-' }}</td></tr>
    </table>
    <hr>
    <table>
        <thead>
            <tr>
                <th style="text-align:left">Item</th>
                <th style="text-align:center">Qty</th>
                <th style="text-align:right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <hr>
    <table class="totals">
        <tr><th style="text-align:left">Total</th><th class="text-right">Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}</th></tr>
        @if($invoice->status === 'paid')
        <tr><td style="text-align:left">Tunai</td><td class="text-right">Rp{{ number_format($invoice->paid_amount, 0, ',', '.') }}</td></tr>
        @if($invoice->change_amount > 0)
        <tr><td style="text-align:left">Kembalian</td><td class="text-right">Rp{{ number_format($invoice->change_amount, 0, ',', '.') }}</td></tr>
        @endif
        <tr><td style="text-align:left">Metode</td><td class="text-right">{{ strtoupper($invoice->payment_method) }}</td></tr>
        @endif
    </table>
    <hr>
    <div class="text-center footer">
        <div>Terima kasih telah berobat di E-KLINIK</div>
        <div>Semoga lekas sembuh!</div>
        @if($invoice->status === 'paid')
        <div class="border-top" style="margin-top:8px; padding-top:8px;">
            <div>Kasir: {{ $invoice->user->name ?? '-' }}</div>
            <div>{{ $invoice->paid_at ? $invoice->paid_at->format('d/m/Y H:i') : '' }}</div>
        </div>
        @endif
    </div>
</body>
</html>