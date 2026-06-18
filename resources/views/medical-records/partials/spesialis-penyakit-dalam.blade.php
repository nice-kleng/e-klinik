@php $sd = old('specialist_data', $specialistData ?? []); @endphp

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-stethoscope me-1 text-primary"></i>Anamnesis Sistem Organ — Penyakit Dalam</h5>
    </div>
    <div class="card-body">
        @php
            $organs = [
                'kardiovaskular' => ['label' => 'Kardiovaskular', 'icon' => 'fa-heart', 'placeholder' => 'Palpitasi, nyeri dada, sesak, edema, sianosis'],
                'respirasi' => ['label' => 'Respirasi', 'icon' => 'fa-lungs', 'placeholder' => 'Batuk, dahak, sesak napas, wheezing, hemoptisis'],
                'gastrointestinal' => ['label' => 'Gastrointestinal', 'icon' => 'fa-stomach', 'placeholder' => 'Mual, muntah, nyeri ulu hati, diare, konstipasi, disfagia'],
                'hepatobilier' => ['label' => 'Hepatobilier', 'icon' => 'fa-liver', 'placeholder' => 'Ikterus, hepatomegali, ascites, spider nevi'],
                'urogenital' => ['label' => 'Urogenital', 'icon' => 'fa-kidneys', 'placeholder' => 'Nyeri BAK, frekuensi, hematuria, fluor albus'],
                'muskuloskeletal' => ['label' => 'Muskuloskeletal', 'icon' => 'fa-bone', 'placeholder' => 'Nyeri sendi, bengkak, kaku pagi, keterbatasan gerak'],
                'neurologi' => ['label' => 'Neurologi', 'icon' => 'fa-brain', 'placeholder' => 'Nyeri kepala, pusing, kelemahan, kesemutan, kejang'],
                'endokrin' => ['label' => 'Endokrin', 'icon' => 'fa-thyroid', 'placeholder' => 'Polidipsi, polifagi, poliuri, tremor, pembesaran leher'],
                'hematologi' => ['label' => 'Hematologi', 'icon' => 'fa-droplet', 'placeholder' => 'Pucat, mudah memar, perdarahan, pembesaran KGB'],
                'integumen' => ['label' => 'Integumen', 'icon' => 'fa-hand', 'placeholder' => 'Ruam, gatal, perubahan warna kulit, ulkus, lesi'],
                'psikiatri' => ['label' => 'Psikiatri', 'icon' => 'fa-face-smile', 'placeholder' => 'Cemas, depresi, gangguan tidur, halusinasi'],
            ];
        @endphp
        <div class="row g-3">
            @foreach($organs as $key => $organ)
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="form-check mb-2">
                            <input type="hidden" name="specialist_data[sistem_organ][{{ $key }}][checked]" value="0">
                            <input type="checkbox" class="form-check-input organ-check" id="organ-{{ $key }}"
                                name="specialist_data[sistem_organ][{{ $key }}][checked]" value="1"
                                {{ old("specialist_data.sistem_organ.{$key}.checked", $sd['sistem_organ'][$key]['checked'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label fw-medium" for="organ-{{ $key }}">
                                <i class="fas {{ $organ['icon'] }} me-1 text-muted"></i>{{ $organ['label'] }}
                            </label>
                        </div>
                        <textarea name="specialist_data[sistem_organ][{{ $key }}][notes]" class="form-control form-control-sm organ-notes" rows="2"
                            placeholder="{{ $organ['placeholder'] }}">{{ old("specialist_data.sistem_organ.{$key}.notes", $sd['sistem_organ'][$key]['notes'] ?? '') }}</textarea>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-heartbeat me-1 text-danger"></i>Pemeriksaan Fisik</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Kesadaran</label>
                <select name="specialist_data[fisik][kesadaran]" class="form-select form-select-sm">
                    @foreach(['Compos Mentis', 'Apatis', 'Somnolen', 'Sopor', 'Koma'] as $opt)
                        <option value="{{ $opt }}" {{ old('specialist_data.fisik.kesadaran', $sd['fisik']['kesadaran'] ?? '') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">TD (mmHg)</label>
                <input type="text" name="specialist_data[fisik][td]" class="form-control form-control-sm" value="{{ old('specialist_data.fisik.td', $sd['fisik']['td'] ?? '') }}" placeholder="120/80">
            </div>
            <div class="col-md-2">
                <label class="form-label">Nadi (/menit)</label>
                <input type="number" name="specialist_data[fisik][nadi]" class="form-control form-control-sm" value="{{ old('specialist_data.fisik.nadi', $sd['fisik']['nadi'] ?? '') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Suhu (°C)</label>
                <input type="text" name="specialist_data[fisik][suhu]" class="form-control form-control-sm" value="{{ old('specialist_data.fisik.suhu', $sd['fisik']['suhu'] ?? '') }}" placeholder="36.5">
            </div>
            <div class="col-md-2">
                <label class="form-label">SpO₂ (%)</label>
                <input type="number" name="specialist_data[fisik][spo2]" class="form-control form-control-sm" value="{{ old('specialist_data.fisik.spo2', $sd['fisik']['spo2'] ?? '') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status Gizi</label>
                <select name="specialist_data[fisik][status_gizi]" class="form-select form-select-sm">
                    @foreach(['Baik', 'Kurang', 'Buruk', 'Obesitas'] as $opt)
                        <option value="{{ $opt }}" {{ old('specialist_data.fisik.status_gizi', $sd['fisik']['status_gizi'] ?? '') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12">
                <h6 class="text-muted small mt-2">Thorax</h6>
            </div>
            <div class="col-md-3">
                <label class="form-label">Inspeksi</label>
                <textarea name="specialist_data[fisik][thorax_inspeksi]" class="form-control form-control-sm" rows="2">{{ old('specialist_data.fisik.thorax_inspeksi', $sd['fisik']['thorax_inspeksi'] ?? '') }}</textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label">Palpasi</label>
                <textarea name="specialist_data[fisik][thorax_palpasi]" class="form-control form-control-sm" rows="2">{{ old('specialist_data.fisik.thorax_palpasi', $sd['fisik']['thorax_palpasi'] ?? '') }}</textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label">Perkusi</label>
                <textarea name="specialist_data[fisik][thorax_perkusi]" class="form-control form-control-sm" rows="2">{{ old('specialist_data.fisik.thorax_perkusi', $sd['fisik']['thorax_perkusi'] ?? '') }}</textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label">Auskultasi</label>
                <textarea name="specialist_data[fisik][thorax_auskultasi]" class="form-control form-control-sm" rows="2">{{ old('specialist_data.fisik.thorax_auskultasi', $sd['fisik']['thorax_auskultasi'] ?? '') }}</textarea>
            </div>
            <div class="col-md-12">
                <h6 class="text-muted small mt-2">Abdomen</h6>
            </div>
            <div class="col-md-3">
                <label class="form-label">Inspeksi</label>
                <textarea name="specialist_data[fisik][abdomen_inspeksi]" class="form-control form-control-sm" rows="2">{{ old('specialist_data.fisik.abdomen_inspeksi', $sd['fisik']['abdomen_inspeksi'] ?? '') }}</textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label">Palpasi</label>
                <textarea name="specialist_data[fisik][abdomen_palpasi]" class="form-control form-control-sm" rows="2">{{ old('specialist_data.fisik.abdomen_palpasi', $sd['fisik']['abdomen_palpasi'] ?? '') }}</textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label">Perkusi</label>
                <textarea name="specialist_data[fisik][abdomen_perkusi]" class="form-control form-control-sm" rows="2">{{ old('specialist_data.fisik.abdomen_perkusi', $sd['fisik']['abdomen_perkusi'] ?? '') }}</textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label">Auskultasi</label>
                <textarea name="specialist_data[fisik][abdomen_auskultasi]" class="form-control form-control-sm" rows="2">{{ old('specialist_data.fisik.abdomen_auskultasi', $sd['fisik']['abdomen_auskultasi'] ?? '') }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Ekstremitas</label>
                <textarea name="specialist_data[fisik][ekstremitas]" class="form-control form-control-sm" rows="2">{{ old('specialist_data.fisik.ekstremitas', $sd['fisik']['ekstremitas'] ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.organ-check').forEach(cb => {
    const notes = cb.closest('.border').querySelector('.organ-notes');
    if (notes) {
        notes.disabled = !cb.checked;
        cb.addEventListener('change', () => { notes.disabled = !cb.checked; });
    }
});
</script>
@endpush