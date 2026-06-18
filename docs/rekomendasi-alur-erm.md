# Alur ERM di Klinik

## 1. Registrasi Pasien

Petugas pendaftaran mengisi:

* Nomor rekam medis
* Nama pasien
* NIK
* Tanggal lahir
* Jenis kelamin
* Alamat
* Nomor telepon
* Penanggung biaya (umum, asuransi, BPJS)
* Kontak darurat

Dokumen/form yang diisi:

* Data identitas pasien
* Persetujuan umum pelayanan (General Consent)
* Surat jaminan/asuransi (jika ada)

---

## 2. Asesmen Awal Perawat / Triage

Biasanya dilakukan sebelum pasien bertemu dokter.

### Data TTV (Tanda-Tanda Vital)

Yang diinput:

* Tekanan darah
* Nadi
* Respirasi
* Suhu
* Saturasi oksigen (SpO₂)
* Berat badan
* Tinggi badan
* BMI

### Keluhan Awal

* Keluhan utama
* Lama keluhan
* Skala nyeri (jika ada)
* Riwayat alergi

### Screening

Tergantung kebijakan klinik:

* Risiko jatuh
* Status nutrisi
* Riwayat penyakit menular
* Status kehamilan
* Skrining merokok

Dokumen ERM:

* Form Asesmen Keperawatan Awal
* Form TTV
* Form Skrining

---

## 3. Pemeriksaan Dokter

Dokter mengisi SOAP atau format asesmen medis.

### Subjective (S)

* Keluhan utama
* Riwayat penyakit sekarang
* Riwayat penyakit dahulu
* Riwayat pengobatan
* Riwayat alergi

### Objective (O)

* Hasil pemeriksaan fisik
* TTV dari perawat
* Hasil penunjang (jika ada)

### Assessment (A)

* Diagnosis kerja
* Diagnosis banding
* Kode ICD-10 (bila diterapkan)

### Plan (P)

* Obat
* Pemeriksaan penunjang
* Edukasi
* Kontrol ulang
* Rujukan

Dokumen ERM:

* Catatan Dokter
* SOAP Note
* Diagnosis
* Order/Tindakan

---

## 4. Pemeriksaan Penunjang (Jika Ada)

Misalnya laboratorium atau radiologi.

Yang diinput:

* Jenis pemeriksaan
* Hasil pemeriksaan
* Interpretasi

Dokumen ERM:

* Permintaan Laboratorium
* Hasil Laboratorium
* Permintaan Radiologi
* Hasil Radiologi

---

## 5. Tindakan Medis

Jika dilakukan tindakan.

Contoh:

* Nebulizer
* Injeksi
* Jahit luka
* Ganti balutan
* Ekstraksi gigi

Yang dicatat:

* Nama tindakan
* Waktu tindakan
* Operator
* Hasil tindakan
* Kondisi setelah tindakan

Dokumen ERM:

* Catatan Tindakan
* Informed Consent (untuk tindakan tertentu)

---

## 6. Resep dan Farmasi

Dokter membuat resep elektronik.

Data yang dicatat:

* Nama obat
* Dosis
* Frekuensi
* Lama penggunaan
* Jumlah obat

Farmasi mengisi:

* Verifikasi resep
* Penyerahan obat
* Edukasi penggunaan obat

Dokumen ERM:

* Resep Elektronik
* Catatan Farmasi

---

## 7. Edukasi Pasien

Yang dicatat:

* Diagnosis yang dijelaskan
* Cara minum obat
* Diet
* Aktivitas
* Jadwal kontrol

Dokumen ERM:

* Form Edukasi Pasien

---

## 8. Penutupan Kunjungan

Dokter menyelesaikan encounter.

Yang diisi:

* Diagnosis akhir
* Status pulang
* Rencana kontrol
* Rujukan (jika ada)

Dokumen ERM:

* Resume Kunjungan
* Surat Rujukan
* Surat Sakit
* Surat Keterangan Sehat

---

# Struktur Minimal ERM Klinik

Kalau Anda sedang membuat sistem ERM klinik, biasanya modul minimalnya:

### Master Data

* Pasien
* Dokter
* Perawat
* Poli
* Obat
* Tindakan
* Diagnosa ICD-10

### Transaksi Kunjungan

#### Registrasi

* Data kunjungan

#### Asesmen Perawat

* TTV
* Keluhan
* Skrining

#### Asesmen Dokter

* SOAP
* Diagnosa
* Tindakan

#### Penunjang

* Lab
* Radiologi

#### Resep

* Obat
* Aturan pakai

#### Dokumen

* Consent
* Surat sakit
* Rujukan
* Resume medis

---

# Contoh Alur Data yang Sering Dipakai

```text
Pasien Datang
      ↓
Registrasi
      ↓
Asesmen Perawat
(TTV + Skrining)
      ↓
Pemeriksaan Dokter
(SOAP)
      ↓
Diagnosa
      ↓
┌─────────────┬─────────────┐
↓             ↓             ↓
Tindakan      Lab          Resep
↓             ↓             ↓
Hasil         Hasil         Farmasi
└─────────────┴─────────────┘
      ↓
Edukasi Pasien
      ↓
Resume Kunjungan
      ↓
Selesai
```

Kalau konteksnya untuk **klinik di Indonesia yang mengikuti regulasi SATUSEHAT dan RME Kemenkes**, saya bisa jelaskan juga **dokumen wajib yang harus ada menurut regulasi (asesmen awal, SOAP, CPPT, informed consent, resume medis, ICD-10, ICD-9-CM, tanda tangan elektronik, audit trail, dll.)** beserta contoh field database ERM yang biasanya dipakai developer saat membangun aplikasi klinik.
