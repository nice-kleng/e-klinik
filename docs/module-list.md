# LIST MODULE PROJECT

## Module Core & Front Office

1. Pendaftaran Pasien (Baru/Lama)
2. Pencatatan data sosial pasien (Baru/Lama) & Cetak Kartu Pasien.
3. Manajemen antrean pasien secara real-time multi Poli.
4. Dashboard monitor antrean untuk ruang tunggu utama klinik.
5. Manajemen data master

## Modul Rekam Medis Elektronik (RME) Lengkap

1. Input Medis standar Kemenkes (Subjective, Objective, Assessment, Plan).
2. Formulir Dinamis Khusus Spesialis:
   2.1 Penyakit Dalam: Ceklis anamnesis sistem organ & riwayat obat kronis.
   2.2 Anak: Input antropometri (BB/TB) & riwayat imunisasi dasar.
   2.3 Saraf: Kalkulator skor kesadaran GCS & 12 Saraf Kranial.
   2.4 Gigi: Modul Odontogram Visual (Peta 32 gigi interaktif).
3. Kodifikasi Diagnosis standar global berbasis ICD-10 & ICD-9-CM.

## Modul e-Resep Elektronik (Integrated)

1. Input resep digital langsung dari ruang dokter saat mengisi RME.
2. Fitur Obat Kronis: Tombol duplikasi/salinan resep bulan lalu untuk pasien rutin.
3. Fitur Obat Racikan: Input formulasis dosis per bungkus (puyer/kapsul) yang otomatis mengalkulasi total kebutuhan tablet.

## Modul Farmasi & Manajemen Stok (Inventory)

1. Sistem Pelacakan Multi-Batch: Pencatatan nomor batch obat, tanggal kedaluwarsa (expired date), dan harga beli yang berbeda dari distributor.
2. Logika Pengeluaran FEFO (First Expired, First Out): Sistem otomatis merekomendasikan stok obat yang paling dekat masa kadaluwarsanya untuk keluar terlebih dahulu.
3. Manajemen stok opname berkala & notifikasi otomatis saat stok mendekati batas minimum (minimum stock alert).

## Modul Apotek & Kasir Pembayaran

1. Penerimaan data e-Resep (biasa & racikan) dari ruang dokter secara real-time.
2. Kalkulasi harga obat otomatis berdasarkan preferensi klinik (Margin Persentase / Harga Flat).
3. Perhitungan otomatis biaya jasa resep (Tuslah) dan biaya penggerusan obat (Embalase).
4. Cetak struk pembayaran, kwitansi tindakan, dan cetak etiket/aturan pakai obat untuk pasien.

## Modul Laboratorium & Radiologi (Penunjang)

1. Order Sistem Internal: Terima rujukan pemeriksaan lab/rontgen langsung dari poli spesialis.
2. Input hasil teks bacaan (Expertise) oleh Dokter Radiologi/Analis Lab.
3. Fitur Upload Berkas Digital: Upload hasil foto rontgen/USG (format gambar/PDF) yang otomatis tersemat di dalam RME pasien.

## Modul Bridging SATUSEHAT Kemenkes

1. Integrasi API resmi SATUSEHAT Kemenkes RI.
2. Otomatisasi pengiriman data kunjungan, diagnosis (ICD-10), dan terapi obat pasien ke server Kemenkes.
3. Fitur Antrean Sinkronisasi: Menjaga sistem tetap berjalan normal di server lokal saat internet klinik terputus, dan mengirimkan data tertunda secara otomatis saat internet kembali aktif.

## Modul Laporan Manajemen & Dashboard Eksekutif

1. Laporan Operasional & Kunjungan: Grafik tren jumlah kunjungan pasien harian/bulanan, demografi pasien, dan statistik poli paling ramai.
2. Laporan Medis (Top 10 Demam/Penyakit): Laporan otomatis 10 besar penyakit terbanyak berdasarkan kode ICD-10 untuk kebutuhan pelaporan internal dan eksternal.
3. Laporan Keuangan & Pendapatan: Rekapitulasi total pendapatan harian/bulanan (tunai/debit/transfer), total omset kasir, dan laporan HPP (Harga Pokok Penjualan) obat.
4. Laporan Logistik Farmasi: Laporan obat Fast-Moving (paling cepat laku) vs Slow-Moving, laporan obat hampir kedaluwarsa, dan riwayat transaksi stok opname.
5. Fitur Ekspor Data: Semua laporan dapat diunduh (eksport) ke format Excel atau PDF dalam satu klik.
