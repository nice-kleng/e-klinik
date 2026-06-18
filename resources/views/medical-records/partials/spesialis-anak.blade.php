@php $sd = old('specialist_data', $specialistData ?? []); @endphp

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-baby me-1 text-info"></i>Anamnesis Spesialis Anak</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-12">
                <h6 class="text-muted small">Riwayat Perinatal</h6>
            </div>
            <div class="col-md-3">
                <label class="form-label">Usia Kehamilan</label>
                <input type="text" name="specialist_data[perinatal][usia_kehamilan]" class="form-control form-control-sm" value="{{ old('specialist_data.perinatal.usia_kehamilan', $sd['perinatal']['usia_kehamilan'] ?? '') }}" placeholder="e.g. 39 minggu">
            </div>
            <div class="col-md-3">
                <label class="form-label">Jenis Persalinan</label>
                <select name="specialist_data[perinatal][jenis_persalinan]" class="form-select form-select-sm">
                    <option value="">-- Pilih --</option>
                    @foreach(['Spontan normal', 'SC', 'Vakum', 'Forceps', 'Lainnya'] as $opt)
                        <option value="{{ $opt }}" {{ old('specialist_data.perinatal.jenis_persalinan', $sd['perinatal']['jenis_persalinan'] ?? '') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Berat Lahir (kg)</label>
                <input type="text" name="specialist_data[perinatal][bb_lahir]" class="form-control form-control-sm" value="{{ old('specialist_data.perinatal.bb_lahir', $sd['perinatal']['bb_lahir'] ?? '') }}" placeholder="3.2">
            </div>
            <div class="col-md-2">
                <label class="form-label">Panjang Lahir (cm)</label>
                <input type="text" name="specialist_data[perinatal][pb_lahir]" class="form-control form-control-sm" value="{{ old('specialist_data.perinatal.pb_lahir', $sd['perinatal']['pb_lahir'] ?? '') }}" placeholder="50">
            </div>
            <div class="col-md-2">
                <label class="form-label">ASI Eksklusif</label>
                <select name="specialist_data[perinatal][asi_eksklusif]" class="form-select form-select-sm">
                    <option value="">--</option>
                    <option value="Ya" {{ old('specialist_data.perinatal.asi_eksklusif', $sd['perinatal']['asi_eksklusif'] ?? '') == 'Ya' ? 'selected' : '' }}>Ya</option>
                    <option value="Tidak" {{ old('specialist_data.perinatal.asi_eksklusif', $sd['perinatal']['asi_eksklusif'] ?? '') == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                </select>
            </div>

            <div class="col-md-12 mt-3">
                <h6 class="text-muted small">Imunisasi</h6>
            </div>
            @php
                $imunisasiList = ['BCG', 'Polio 1', 'Polio 2', 'Polio 3', 'Polio 4', 'DPT-HB-Hib 1', 'DPT-HB-Hib 2', 'DPT-HB-Hib 3', 'Campak/MR', 'Booster DPT', 'Booster Polio', 'IPV', 'Varicella', 'Hepatitis A', 'MMR', 'Tifoid'];
            @endphp
            <div class="col-md-12">
                <div class="row g-2">
                    @foreach($imunisasiList as $imun)
                        <div class="col-md-4 col-lg-3">
                            <div class="form-check">
                                <input type="hidden" name="specialist_data[imunisasi][{{ Str::slug($imun) }}][checked]" value="0">
                                <input type="checkbox" class="form-check-input" id="imun-{{ Str::slug($imun) }}" name="specialist_data[imunisasi][{{ Str::slug($imun) }}][checked]" value="1"
                                    {{ old("specialist_data.imunisasi." . Str::slug($imun) . ".checked", $sd['imunisasi'][Str::slug($imun)]['checked'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="imun-{{ Str::slug($imun) }}">{{ $imun }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-md-12 mt-3">
                <h6 class="text-muted small">Tumbuh Kembang</h6>
            </div>
            <div class="col-md-3">
                <label class="form-label">Motorik Kasar</label>
                <textarea name="specialist_data[tumbuh_kembang][motorik_kasar]" class="form-control form-control-sm" rows="2" placeholder="Berguling, duduk, merangkak, berjalan">{{ old('specialist_data.tumbuh_kembang.motorik_kasar', $sd['tumbuh_kembang']['motorik_kasar'] ?? '') }}</textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label">Motorik Halus</label>
                <textarea name="specialist_data[tumbuh_kembang][motorik_halus]" class="form-control form-control-sm" rows="2" placeholder="Memegang, menjumput, menggambar">{{ old('specialist_data.tumbuh_kembang.motorik_halus', $sd['tumbuh_kembang']['motorik_halus'] ?? '') }}</textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label">Bicara & Bahasa</label>
                <textarea name="specialist_data[tumbuh_kembang][bicara]" class="form-control form-control-sm" rows="2" placeholder="Mengoceh, kata pertama, kalimat">{{ old('specialist_data.tumbuh_kembang.bicara', $sd['tumbuh_kembang']['bicara'] ?? '') }}</textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label">Sosial & Kemandirian</label>
                <textarea name="specialist_data[tumbuh_kembang][sosial]" class="form-control form-control-sm" rows="2" placeholder="Senyum, bermain, interaksi">{{ old('specialist_data.tumbuh_kembang.sosial', $sd['tumbuh_kembang']['sosial'] ?? '') }}</textarea>
            </div>

            <div class="col-md-12 mt-3">
                <h6 class="text-muted small">Antropometri</h6>
            </div>
            <div class="col-md-3">
                <label class="form-label">BB (kg)</label>
                <input type="text" name="specialist_data[antropometri][bb]" class="form-control form-control-sm" value="{{ old('specialist_data.antropometri.bb', $sd['antropometri']['bb'] ?? '') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">TB/PB (cm)</label>
                <input type="text" name="specialist_data[antropometri][tb]" class="form-control form-control-sm" value="{{ old('specialist_data.antropometri.tb', $sd['antropometri']['tb'] ?? '') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">LK (cm)</label>
                <input type="text" name="specialist_data[antropometri][lk]" class="form-control form-control-sm" value="{{ old('specialist_data.antropometri.lk', $sd['antropometri']['lk'] ?? '') }}" placeholder="Lingkar Kepala">
            </div>
            <div class="col-md-3">
                <label class="form-label">LILA (cm)</label>
                <input type="text" name="specialist_data[antropometri][lila]" class="form-control form-control-sm" value="{{ old('specialist_data.antropometri.lila', $sd['antropometri']['lila'] ?? '') }}" placeholder="Lingkar Lengan Atas">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status Gizi (BB/U)</label>
                <select name="specialist_data[antropometri][status_gizi]" class="form-select form-select-sm">
                    <option value="">-- Pilih --</option>
                    @foreach(['Baik', 'Kurang', 'Buruk', 'Sangat Buruk', 'Risiko Gizi Lebih', 'Gizi Lebih', 'Obesitas'] as $opt)
                        <option value="{{ $opt }}" {{ old('specialist_data.antropometri.status_gizi', $sd['antropometri']['status_gizi'] ?? '') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Interpretasi (WHO)</label>
                <input type="text" name="specialist_data[antropometri][interpretasi]" class="form-control form-control-sm" value="{{ old('specialist_data.antropometri.interpretasi', $sd['antropometri']['interpretasi'] ?? '') }}" placeholder="Z-score">
            </div>
            <div class="col-md-12">
                <label class="form-label">Catatan Tumbuh Kembang</label>
                <textarea name="specialist_data[antropometri][catatan]" class="form-control form-control-sm" rows="2">{{ old('specialist_data.antropometri.catatan', $sd['antropometri']['catatan'] ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>