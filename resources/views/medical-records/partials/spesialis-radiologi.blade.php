@php $sd = old('specialist_data', $specialistData ?? []); @endphp

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-x-ray me-1 text-primary"></i>Pemeriksaan Radiologi</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-12">
                <h6 class="text-muted small">Indikasi & Data Klinis</h6>
            </div>
            <div class="col-md-6">
                <label class="form-label">Indikasi Pemeriksaan</label>
                <textarea name="specialist_data[indikasi]" class="form-control" rows="3" placeholder="Indikasi klinis dilakukannya pemeriksaan radiologi...">{{ old('specialist_data.indikasi', $sd['indikasi'] ?? '') }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Riwayat Penyakit Terkait</label>
                <textarea name="specialist_data[riwayat]" class="form-control" rows="3" placeholder="Riwayat trauma, operasi, keganasan, radiasi sebelumnya...">{{ old('specialist_data.riwayat', $sd['riwayat'] ?? '') }}</textarea>
            </div>

            <div class="col-md-12 mt-3">
                <h6 class="text-muted small">Jenis Pemeriksaan</h6>
            </div>
            <div class="col-md-3">
                <label class="form-label">Modalitas</label>
                <select name="specialist_data[jenis][modalitas]" class="form-select">
                    <option value="">-- Pilih --</option>
                    @foreach(['Foto Polos (X-Ray)', 'CT Scan', 'MRI', 'USG', 'Mammografi', 'Fluoroskopi', 'Angiografi', 'PET-CT', 'Lainnya'] as $opt)
                        <option value="{{ $opt }}" {{ old('specialist_data.jenis.modalitas', $sd['jenis']['modalitas'] ?? '') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Region / Bagian Tubuh</label>
                <input type="text" name="specialist_data[jenis][region]" class="form-control" value="{{ old('specialist_data.jenis.region', $sd['jenis']['region'] ?? '') }}" placeholder="Thorax, Abdomen, Kepala, Cervical...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Posisi / Proyeksi</label>
                <input type="text" name="specialist_data[jenis][proyeksi]" class="form-control" value="{{ old('specialist_data.jenis.proyeksi', $sd['jenis']['proyeksi'] ?? '') }}" placeholder="AP, Lateral, Oblique, 3D...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Kontras</label>
                <select name="specialist_data[jenis][kontras]" class="form-select">
                    <option value="">-- Pilih --</option>
                    <option value="Tanpa Kontras" {{ old('specialist_data.jenis.kontras', $sd['jenis']['kontras'] ?? '') == 'Tanpa Kontras' ? 'selected' : '' }}>Tanpa Kontras</option>
                    <option value="Dengan Kontras" {{ old('specialist_data.jenis.kontras', $sd['jenis']['kontras'] ?? '') == 'Dengan Kontras' ? 'selected' : '' }}>Dengan Kontras</option>
                    <option value="Non-Kontras + Kontras" {{ old('specialist_data.jenis.kontras', $sd['jenis']['kontras'] ?? '') == 'Non-Kontras + Kontras' ? 'selected' : '' }}>Non-Kontras + Kontras</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Jenis Kontras</label>
                <input type="text" name="specialist_data[jenis][jenis_kontras]" class="form-control" value="{{ old('specialist_data.jenis.jenis_kontras', $sd['jenis']['jenis_kontras'] ?? '') }}" placeholder="Barium, iodine, gadolinium...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Dosis Radiasi (mGy)</label>
                <input type="text" name="specialist_data[jenis][dosis]" class="form-control" value="{{ old('specialist_data.jenis.dosis', $sd['jenis']['dosis'] ?? '') }}" placeholder="Jika ada">
            </div>

            <div class="col-md-12 mt-3">
                <h6 class="text-muted small">Temuan Radiologis</h6>
            </div>
            <div class="col-md-12">
                <label class="form-label">Deskripsi Temuan</label>
                <textarea name="specialist_data[temuan][deskripsi]" class="form-control" rows="4" placeholder="Deskripsi detail temuan radiologis...">{{ old('specialist_data.temuan.deskripsi', $sd['temuan']['deskripsi'] ?? '') }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Temuan Normal / Abnormal</label>
                <select name="specialist_data[temuan][kesan]" class="form-select">
                    <option value="">-- Pilih --</option>
                    <option value="Normal" {{ old('specialist_data.temuan.kesan', $sd['temuan']['kesan'] ?? '') == 'Normal' ? 'selected' : '' }}>Normal</option>
                    <option value="Abnormal - Jinak" {{ old('specialist_data.temuan.kesan', $sd['temuan']['kesan'] ?? '') == 'Abnormal - Jinak' ? 'selected' : '' }}>Abnormal — Jinak</option>
                    <option value="Abnormal - Ganas" {{ old('specialist_data.temuan.kesan', $sd['temuan']['kesan'] ?? '') == 'Abnormal - Ganas' ? 'selected' : '' }}>Abnormal — Ganas</option>
                    <option value="Abnormal - Lainnya" {{ old('specialist_data.temuan.kesan', $sd['temuan']['kesan'] ?? '') == 'Abnormal - Lainnya' ? 'selected' : '' }}>Abnormal — Lainnya</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Kategori Temuan</label>
                <select name="specialist_data[temuan][kategori]" class="form-select">
                    <option value="">-- Pilih --</option>
                    @foreach(['Fraktur', 'Tumor/Massa', 'Inflamasi/Infeksi', 'Degeneratif', 'Kongenital', 'Vaskuler', 'Lainnya'] as $opt)
                        <option value="{{ $opt }}" {{ old('specialist_data.temuan.kategori', $sd['temuan']['kategori'] ?? '') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Kesimpulan</label>
                <textarea name="specialist_data[temuan][kesimpulan]" class="form-control" rows="2">{{ old('specialist_data.temuan.kesimpulan', $sd['temuan']['kesimpulan'] ?? '') }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Rekomendasi</label>
                <textarea name="specialist_data[temuan][rekomendasi]" class="form-control" rows="2" placeholder="Pemeriksaan lanjutan, biopsi, follow-up...">{{ old('specialist_data.temuan.rekomendasi', $sd['temuan']['rekomendasi'] ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>