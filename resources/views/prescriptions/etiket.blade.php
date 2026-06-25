<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Etiket Obat</title>
    <style>
        @page { size: 60mm 40mm; margin: 3mm; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 9px; margin: 0; padding: 3mm; }
        .label { width: 54mm; min-height: 34mm; border: 1px solid #000; padding: 2mm; page-break-after: always; }
        .header { text-align: center; font-weight: bold; font-size: 10px; border-bottom: 1px solid #000; margin-bottom: 1mm; padding-bottom: 1mm; }
        .patient { font-weight: bold; font-size: 11px; margin-bottom: 1mm; }
        .drug { margin-bottom: 1mm; }
        .drug-name { font-weight: bold; font-size: 10px; }
        .usage { margin-bottom: 1mm; padding: 1mm; background: #f9f9f9; }
        .footer { font-size: 7px; color: #666; text-align: center; margin-top: 1mm; border-top: 1px solid #ccc; padding-top: 1mm; }
        .qty { font-size: 8px; color: #333; }
    </style>
</head>
<body>
    @forelse($items as $item)
    <div class="label">
        <div class="header">E-KLINIK</div>
        <div class="patient">{{ $patient->name ?? 'Pasien' }}</div>
        <div class="drug">
            <div class="drug-name">{{ $item->medicine->name ?? $item->description ?? 'Obat' }}</div>
            <div class="qty">{{ $item->quantity }} {{ $item->unit ?? $item->medicine->unit ?? '' }}</div>
        </div>
        <div class="usage">
            <strong>Aturan Pakai:</strong><br>
            {{ is_array($item->dosage) ? json_encode($item->dosage) : $item->dosage ?? '-' }}
            @if($item->instruction)<br><em>{{ $item->instruction }}</em>@endif
        </div>
        <div class="footer">
            {{ $item->prescription->prescription_number ?? '' }} | {{ now()->format('d/m/Y') }}
        </div>
    </div>
    @empty
    <div class="label">Tidak ada item</div>
    @endforelse
    <script>window.onload = function() { setTimeout(function() { window.print(); }, 500); };</script>
</body>
</html>