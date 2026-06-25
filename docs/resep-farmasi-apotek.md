# Modul Resep → Farmasi → Apotek → Kasir

Dokumen teknis alur e-Resep, dispensing, pricing, inventory, dan pembayaran.

---

## 1. Alur Lengkap Resep → Bayar

```
DOKTER BUAT RESEP (RME workspace)
      │
      ▼
┌──────────────────────────────────┐
│ 1. Resep Aktif (status=active)   │
│    - Non-racikan: obat + dosis   │
│    - Racikan: bahan + qty kalkulasi │
│    - View: modal fullscreen AJAX  │
│    - Auto-calc via PricingService │
│      (bisa di-toggle off)         │
└──────────────┬───────────────────┘
               │ Notifikasi sidebar
               ▼
┌──────────────────────────────────┐
│ 2. Farmasi — Resep Masuk         │
│    Route: /prescriptions/pending │
│    - Lihat semua resep active    │
│    - Klik "Proses" → dispense    │
└──────────────┬───────────────────┘
               │
               ▼
┌──────────────────────────────────┐
│ 3. Dispensing                    │
│    - Cek stok (inventoryService) │
│    - Deduksi FEFO                │
│      (batch expired terlua dulu) │
│    - Catat inventory_transactions│
│    - UPDATE status = dispensed   │
│    - UPDATE service_status =     │
│      cashier (via Registration)  │
│    - Cetak etiket per item       │
└──────────────┬───────────────────┘
               │
               ▼
┌──────────────────────────────────┐
│ 4. Kasir — Buat Invoice          │
│    Route: /kasir/create          │
│    - Pilih registration          │
│    - Ceklis item:                │
│      · Resep (prescription_items)│
│      · Tindakan (procedures)     │
│      · Lab (lab_request_items)   │
│    - Auto-hitung total           │
│    - Simpan → status=pendi ng    │
└──────────────┬───────────────────┘
               │
               ▼
┌──────────────────────────────────┐
│ 5. Kasir — Pembayaran            │
│    Route: /kasir/{invoice}/show  │
│    - Pilih metode bayar          │
│      (cash/debit/kredit/transfer)│
│    - Input jumlah dibayar        │
│    - Hitung kembalian auto       │
│    - UPDATE status = paid        │
│    - UPDATE service_status =     │
│      education                   │
│    - Cetak struk (80mm)          │
└──────────────┬───────────────────┘
               │
               ▼
         Education → Completed
```

---

## 2. Pricing & Biaya

### 2.1 Komponen Biaya Resep

| Komponen | Sumber | Besaran |
|----------|--------|---------|
| Harga Obat | `FIFO selling_price` dari inventory batch | Variabel per batch |
| Tuslah | Config `pharmacy.tuslah` | Rp 3.000 (default) |
| Embalase | Config `pharmacy.embalase` | Rp 1.000 (default) |

### 2.2 FIFO Pricing

`PricingService::getFifoSellingPrice(medicineId)`
- Ambil batch dengan `expired_date ASC`
- Filter: `quantity > 0` dan `expired_date >= today`
- Gunakan `selling_price` dari batch terlama

### 2.3 Auto-Calc

- **Non-racikan**: `subtotal = quantity × FIFO_selling_price + tuslah + embalase`
- **Racikan**: `subtotal = (calculated_qty × FIFO_selling_price) + tuslah + embalase`
  - `calculated_qty = ceil(qty_per_packet × total_packets / dosage_per_unit)`

### 2.4 Toggle Auto-Calc

- Global: `Configuration` model key `pharmacy_auto_calc` (true/false)
- Per-resep: override via `auto_calc` checkbox di form
- Jika auto-calc off, user input harga manual

---

## 3. Inventory (Stok Obat)

### 3.1 Model Relations

```
Medicine (obat)
  └── Inventory (batch) — 1:N
        └── InventoryTransaction (transaksi) — 1:N
              type: in, out, opname
```

### 3.2 Batch Management

| Field | Deskripsi |
|-------|-----------|
| `batch_number` | Nomor batch dari supplier |
| `quantity` | Stok di batch ini |
| `unit_price` | Harga beli |
| `selling_price` | Harga jual (FIFO) |
| `production_date` | Tanggal produksi |
| `expired_date` | Tanggal kadaluwarsa |
| `supplier_id` | Supplier |

### 3.3 Transaksi Stok

| Type | Trigger | Method |
|------|---------|--------|
| `in` | Tambah stok baru | `InventoryService::addStock()` |
| `out` | Dispensing resep | `InventoryService::deductFifo()` |
| `opname` | Stok opname | `InventoryService::opnameStock()` |

### 3.4 FEFO Deduksi

`InventoryService::deductFifo(medicineId, quantity)`
1. Ambil batch dengan `expired_date ASC`, `quantity > 0`
2. Deduksi dari batch paling tua dulu
3. Jika quantity tidak cukup → throw exception
4. Catat transaksi per batch

### 3.5 Minimum Stock Alert

- Kolom `medicines.minimum_stock` (nullable integer)
- Default threshold: 10 jika tidak diisi
- View: `/inventories/reports/low-stock`
- Sidebar badge: menampilkan jumlah obat stok menipis

### 3.6 Stock Opname

- Route: `GET/POST /inventories/opname`
- GET: tampilkan form semua obat aktif + current stock sistem
- POST: terima `items[][medicine_id]` + `items[][actual_qty]`
- Surplus: buat batch baru + transaksi `opname`
- Defisit: deduct FIFO + transaksi `opname`
- JS: selisih otomatis dihitung real-time

---

## 4. Etiket / Label Obat

### 4.1 Per-Item Label

Route: `GET /prescriptions/etiket/{prescription}`

Layout per item (60×40mm):
```
┌─────────────────────────┐
│ E-KLINIK                │
│ Nama Pasien             │
│ ─────────────────────── │
│ Nama Obat               │
│ Qty: 10 tablet          │
│ ─────────────────────── │
│ Aturan Pakai:           │
│ 3×1 sehari              │
│ ─────────────────────── │
│ No.Resep | Tanggal      │
└─────────────────────────┘
```

---

## 5. Invoice & Pembayaran (Kasir)

### 5.1 Model Relations

```
Invoice (header)
  ├── InvoiceItem (item tagihan) — 1:N
  │     └── itemable (polymorphic)
  │           ├── PrescriptionItem
  │           ├── MedicalRecordProcedure
  │           └── LabRequestItem
  └── Registration (1:1)
```

### 5.2 Status Invoice

| Status | Arti |
|--------|------|
| `pending` | Belum dibayar |
| `paid` | Lunas |
| `cancelled` | Dibatalkan |

### 5.3 Flow Pembayaran

1. Buat invoice: pilih registration → ceklis item → simpan → status=pending
2. Bayar: input metode + jumlah → hitung kembalian → status=paid
3. Selesai: auto-update `service_status = education`
4. Cetak: struk 80mm thermal printer

### 5.4 Struk Pembayaran (Print)

```
        E-KLINIK
    Jl. Raya Sehat No. 1
    Telp: (021) 1234567
═══════════════════════════
        INVOICE
    INV-20260625-0001
    25/06/2026 14:30
═══════════════════════════
Pasien        : John Doe
Poli          : Umum
Dokter        : dr. Andi
───────────────────────────
Item              Qty  Total
Paracetamol        10  15,000
...
───────────────────────────
Total                 150,000
Tunai                 200,000
Kembalian             50,000
───────────────────────────
   Terima kasih!
   Semoga lekas sembuh
Kasir: Budi | 25/06/2026 14:32
```

---

## 6. Service Status Lifecycle

### 6.1 Transitions

```
registered ──► triage ──► in_consultation ──► pharmacy ──► cashier ──► education ──► completed
                                                                                        │
                                                                        cancelled ◄──────┘
```

### 6.2 Pemicu Transisi

| Status | Trigger |
|--------|---------|
| `registered` | `QueueService::registerQueue()` |
| `triage` | `TriageService::create()` |
| `in_consultation` | `QueueService::callAndProgress()` |
| `pharmacy` | Dokter selesai konsultasi (perlu implementasi) |
| `cashier` | Dispensing selesai oleh farmasi |
| `education` | Pembayaran sukses oleh kasir |
| `completed` | `VisitSummaryService::create()` |
| `cancelled` | `QueueService::cancel()` |

---

## 7. User Roles & Access

| Role | Modul |
|------|-------|
| `admin` | Semua modul |
| `doctor` | Buat resep (via RME workspace), lihat status |
| `pharmacist` | Proses dispensing, kelola stok, opname, etiket |
| `cashier` | Invoice, pembayaran, cetak struk |
| `receptionist` | Lihat status pembayaran |
