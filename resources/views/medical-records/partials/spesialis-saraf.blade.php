@php $sd = old('specialist_data', $specialistData ?? []); @endphp

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-brain me-1 text-primary"></i>Pemeriksaan Neurologi</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-12">
                <h6 class="text-muted small">Nervus Cranialis</h6>
            </div>
            @php
                $ncList = [
                    ['key' => 'nc1', 'label' => 'N.I — Olfactorius', 'items' => 'Bau, hiposmia, anosmia'],
                    ['key' => 'nc2', 'label' => 'N.II — Opticus', 'items' => 'Visus, lapang pandang, funduskopi, RAPD'],
                    ['key' => 'nc3', 'label' => 'N.III — Oculomotorius', 'items' => 'Ptosis, deviasi bola mata, ukuran pupil, RC'],
                    ['key' => 'nc4', 'label' => 'N.IV — Trochlearis', 'items' => 'Gerakan mata ke inferomedial'],
                    ['key' => 'nc5', 'label' => 'N.V — Trigeminus', 'items' => 'Sensasi wajah, motorik (masseter, temporalis), refleks kornea'],
                    ['key' => 'nc6', 'label' => 'N.VI — Abducens', 'items' => 'Gerakan mata ke lateral, diplopia'],
                    ['key' => 'nc7', 'label' => 'N.VII — Facialis', 'items' => 'Mimik wajah, pengecap 2/3 anterior lidah'],
                    ['key' => 'nc8', 'label' => 'N.VIII — Vestibulocochlearis', 'items' => 'Rinne, Weber, Schwabach, nistagmus, HINTS'],
                    ['key' => 'nc9', 'label' => 'N.IX — Glossopharyngeus', 'items' => 'Refleks muntah, pengecap 1/3 posterior'],
                    ['key' => 'nc10', 'label' => 'N.X — Vagus', 'items' => 'Palatum simetris, suara serak, refleks batuk'],
                    ['key' => 'nc11', 'label' => 'N.XI — Accessorius', 'items' => 'M. trapezius, M. sternocleidomastoid'],
                    ['key' => 'nc12', 'label' => 'N.XII — Hypoglossus', 'items' => 'Gerakan lidah, fasikulasi, deviasi'],
                ];
            @endphp
            @foreach($ncList as $nc)
                <div class="col-md-4">
                    <div class="border rounded-3 p-2 h-100">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <select name="specialist_data[nc][{{ $nc['key'] }}][status]" class="form-select form-select-sm" style="width:auto;">
                                <option value="normal" {{ old("specialist_data.nc.{$nc['key']}.status", $sd['nc'][$nc['key']]['status'] ?? '') == 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="abnormal" {{ old("specialist_data.nc.{$nc['key']}.status", $sd['nc'][$nc['key']]['status'] ?? '') == 'abnormal' ? 'selected' : '' }}>Abnormal</option>
                                <option value="tidak_diperiksa" {{ old("specialist_data.nc.{$nc['key']}.status", $sd['nc'][$nc['key']]['status'] ?? '') == 'tidak_diperiksa' ? 'selected' : '' }}>Tdk diperiksa</option>
                            </select>
                            <strong class="small">{{ $nc['label'] }}</strong>
                        </div>
                        <textarea name="specialist_data[nc][{{ $nc['key'] }}][notes]" class="form-control form-control-sm" rows="1" placeholder="{{ $nc['items'] }}">{{ old("specialist_data.nc.{$nc['key']}.notes", $sd['nc'][$nc['key']]['notes'] ?? '') }}</textarea>
                    </div>
                </div>
            @endforeach

            <div class="col-md-12 mt-3">
                <h6 class="text-muted small">Pemeriksaan Motorik — MRC Scale (0-5)</h6>
            </div>
            @php
                $motorikList = [
                    'atas_kanan' => 'Ekstremitas Atas Kanan',
                    'atas_kiri' => 'Ekstremitas Atas Kiri',
                    'bawah_kanan' => 'Ekstremitas Bawah Kanan',
                    'bawah_kiri' => 'Ekstremitas Bawah Kiri',
                ];
                $mrcOptions = ['0 (Tidak ada gerakan)', '1 (Kedip/flicker)', '2 (Gerak tanpa gravitasi)', '3 (Melawan gravitasi)', '4 (Melawan tahanan ringan)', '5 (Normal)'];
            @endphp
            @foreach($motorikList as $key => $label)
                <div class="col-md-3">
                    <label class="form-label small">{{ $label }}</label>
                    <select name="specialist_data[motorik][{{ $key }}]" class="form-select form-select-sm">
                        <option value="">--</option>
                        @foreach($mrcOptions as $opt)
                            <option value="{{ $opt }}" {{ old("specialist_data.motorik.{$key}", $sd['motorik'][$key] ?? '') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach

            <div class="col-md-12 mt-3">
                <h6 class="text-muted small">Pemeriksaan Sensorik</h6>
            </div>
            @php
                $sensorikFields = ['raba' => 'Raba (taktil)', 'nyeri' => 'Nyeri (tusuk)', 'suhu' => 'Suhu (panas/dingin)', 'vibrasi' => 'Vibrasi (garpu tala)', 'propriosepsi' => 'Propriosepsi (posisi sendi)'];
            @endphp
            @foreach($sensorikFields as $key => $label)
                <div class="col-md-4">
                    <label class="form-label small">{{ $label }}</label>
                    <textarea name="specialist_data[sensorik][{{ $key }}]" class="form-control form-control-sm" rows="1" placeholder="Normal / abnormal + lokasi">{{ old("specialist_data.sensorik.{$key}", $sd['sensorik'][$key] ?? '') }}</textarea>
                </div>
            @endforeach

            <div class="col-md-12 mt-3">
                <h6 class="text-muted small">Refleks</h6>
            </div>
            <div class="col-md-4">
                <label class="form-label small">Fisiologis</label>
                <textarea name="specialist_data[refleks][fisiologis]" class="form-control form-control-sm" rows="2" placeholder="Biseps, triseps, patella, Achilles, pronator">{{ old('specialist_data.refleks.fisiologis', $sd['refleks']['fisiologis'] ?? '') }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label small">Patologis</label>
                <textarea name="specialist_data[refleks][patologis]" class="form-control form-control-sm" rows="2" placeholder="Babinski, Hoffman, Tromner, Oppenheim, Gordon">{{ old('specialist_data.refleks.patologis', $sd['refleks']['patologis'] ?? '') }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label small">Kloni</label>
                <input type="text" name="specialist_data[refleks][kloni]" class="form-control form-control-sm" value="{{ old('specialist_data.refleks.kloni', $sd['refleks']['kloni'] ?? '') }}" placeholder="Pergelangan kaki, patella">
            </div>

            <div class="col-md-12 mt-3">
                <h6 class="text-muted small">Koordinasi & Keseimbangan</h6>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Finger-to-Nose</label>
                <select name="specialist_data[koordinasi][finger_nose]" class="form-select form-select-sm">
                    <option value="">--</option>
                    <option value="normal" {{ old('specialist_data.koordinasi.finger_nose', $sd['koordinasi']['finger_nose'] ?? '') == 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="dismetri" {{ old('specialist_data.koordinasi.finger_nose', $sd['koordinasi']['finger_nose'] ?? '') == 'dismetri' ? 'selected' : '' }}>Dismetri</option>
                    <option value="intensi_tremor" {{ old('specialist_data.koordinasi.finger_nose', $sd['koordinasi']['finger_nose'] ?? '') == 'intensi_tremor' ? 'selected' : '' }}>Intensi Tremor</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Heel-to-Shin</label>
                <select name="specialist_data[koordinasi][heel_shin]" class="form-select form-select-sm">
                    <option value="">--</option>
                    <option value="normal" {{ old('specialist_data.koordinasi.heel_shin', $sd['koordinasi']['heel_shin'] ?? '') == 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="abnormal" {{ old('specialist_data.koordinasi.heel_shin', $sd['koordinasi']['heel_shin'] ?? '') == 'abnormal' ? 'selected' : '' }}>Abnormal</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Romberg</label>
                <select name="specialist_data[koordinasi][romberg]" class="form-select form-select-sm">
                    <option value="">--</option>
                    <option value="negatif" {{ old('specialist_data.koordinasi.romberg', $sd['koordinasi']['romberg'] ?? '') == 'negatif' ? 'selected' : '' }}>Negatif (stabil)</option>
                    <option value="positif" {{ old('specialist_data.koordinasi.romberg', $sd['koordinasi']['romberg'] ?? '') == 'positif' ? 'selected' : '' }}>Positif (jatuh)</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Gait (Cara Jalan)</label>
                <input type="text" name="specialist_data[koordinasi][gait]" class="form-control form-control-sm" value="{{ old('specialist_data.koordinasi.gait', $sd['koordinasi']['gait'] ?? '') }}" placeholder="Normal, hemiparetik, ataksik, steppage">
            </div>
        </div>
    </div>
</div>