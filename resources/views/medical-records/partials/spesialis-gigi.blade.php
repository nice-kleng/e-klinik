@php $sd = old('specialist_data', $specialistData ?? []); @endphp

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-tooth me-1 text-primary"></i>Pemeriksaan Gigi</h5>
    </div>
    <div class="card-body">
        {{-- Odontogram Visual --}}
        <div class="row g-3">
            <div class="col-md-12">
                <h6 class="text-muted small">Odontogram</h6>
                <p class="small text-muted">Klik gigi untuk mengubah status: <span class="badge bg-success">Utuh</span> → <span class="badge bg-danger">Karies</span> → <span class="badge bg-warning text-dark">Tambalan</span> → <span class="badge bg-secondary">Ekstraksi</span> → <span class="badge bg-info">Mahkota</span></p>
            </div>
            <input type="hidden" name="specialist_data[odontogram]" id="odontogramInput" value='{{ old('specialist_data.odontogram', $sd['odontogram'] ?? '{}') }}'>

            @php
                $quadrants = [
                    ['name' => 'RA Kanan', 'teeth' => [18,17,16,15,14,13,12,11]],
                    ['name' => 'RA Kiri', 'teeth' => [21,22,23,24,25,26,27,28]],
                    ['name' => 'RB Kiri', 'teeth' => [31,32,33,34,35,36,37,38]],
                    ['name' => 'RB Kanan', 'teeth' => [48,47,46,45,44,43,42,41]],
                ];
                $statusColors = ['utuh' => '#28a745', 'karies' => '#dc3545', 'tambalan' => '#ffc107', 'ekstraksi' => '#6c757d', 'mahkota' => '#17a2b8'];
            @endphp

            <div class="col-md-12">
                <div class="odontogram-container" style="max-width:700px;margin:0 auto;">
                    @foreach($quadrants as $q)
                        <div class="d-flex justify-content-center gap-1 mb-3">
                            @foreach($q['teeth'] as $toothNum)
                                @php
                                    $toothData = json_decode($sd['odontogram'] ?? '{}', true);
                                    $status = $toothData[(string)$toothNum] ?? 'utuh';
                                @endphp
                                <div class="odontogram-tooth" data-tooth="{{ $toothNum }}" data-status="{{ $status }}"
                                     style="width:38px;height:50px;border:2px solid #dee2e6;border-radius:6px;display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:pointer;background-color:{{ $statusColors[$status] }};color:#fff;font-size:11px;font-weight:bold;transition:all .15s;position:relative;"
                                     onclick="toggleTooth({{ $toothNum }})"
                                     onmouseover="this.style.transform='scale(1.15)'" onmouseout="this.style.transform='scale(1)'">
                                    <span>{{ $toothNum }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-md-12">
                <p class="small text-muted mb-0">
                    <span class="me-3"><span style="display:inline-block;width:14px;height:14px;background:#28a745;border-radius:3px;vertical-align:middle;"></span> Utuh</span>
                    <span class="me-3"><span style="display:inline-block;width:14px;height:14px;background:#dc3545;border-radius:3px;vertical-align:middle;"></span> Karies</span>
                    <span class="me-3"><span style="display:inline-block;width:14px;height:14px;background:#ffc107;border-radius:3px;vertical-align:middle;"></span> Tambalan</span>
                    <span class="me-3"><span style="display:inline-block;width:14px;height:14px;background:#6c757d;border-radius:3px;vertical-align:middle;"></span> Ekstraksi</span>
                    <span class="me-3"><span style="display:inline-block;width:14px;height:14px;background:#17a2b8;border-radius:3px;vertical-align:middle;"></span> Mahkota</span>
                </p>
            </div>
        </div>

        <hr>
        <div class="row g-3">
            <div class="col-md-12">
                <h6 class="text-muted small">Status Jaringan Periodontal</h6>
            </div>
            <div class="col-md-3">
                <label class="form-label">Gingiva</label>
                <textarea name="specialist_data[periodontal][gingiva]" class="form-control form-control-sm" rows="2" placeholder="Warna, edema, perdarahan, resesi">{{ old('specialist_data.periodontal.gingiva', $sd['periodontal']['gingiva'] ?? '') }}</textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label">Kedalaman Poket (mm)</label>
                <input type="text" name="specialist_data[periodontal][poket]" class="form-control form-control-sm" value="{{ old('specialist_data.periodontal.poket', $sd['periodontal']['poket'] ?? '') }}" placeholder="Rata-rata / tertinggi">
            </div>
            <div class="col-md-3">
                <label class="form-label">Kalkulus</label>
                <select name="specialist_data[periodontal][kalkulus]" class="form-select form-select-sm">
                    <option value="">--</option>
                    <option value="Tidak ada" {{ old('specialist_data.periodontal.kalkulus', $sd['periodontal']['kalkulus'] ?? '') == 'Tidak ada' ? 'selected' : '' }}>Tidak ada</option>
                    <option value="Ringan" {{ old('specialist_data.periodontal.kalkulus', $sd['periodontal']['kalkulus'] ?? '') == 'Ringan' ? 'selected' : '' }}>Ringan</option>
                    <option value="Sedang" {{ old('specialist_data.periodontal.kalkulus', $sd['periodontal']['kalkulus'] ?? '') == 'Sedang' ? 'selected' : '' }}>Sedang</option>
                    <option value="Berat" {{ old('specialist_data.periodontal.kalkulus', $sd['periodontal']['kalkulus'] ?? '') == 'Berat' ? 'selected' : '' }}>Berat</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Mobilitas</label>
                <select name="specialist_data[periodontal][mobilitas]" class="form-select form-select-sm">
                    <option value="">--</option>
                    <option value="Tidak ada" {{ old('specialist_data.periodontal.mobilitas', $sd['periodontal']['mobilitas'] ?? '') == 'Tidak ada' ? 'selected' : '' }}>Tidak ada</option>
                    <option value="Derajat 1" {{ old('specialist_data.periodontal.mobilitas', $sd['periodontal']['mobilitas'] ?? '') == 'Derajat 1' ? 'selected' : '' }}>Derajat 1</option>
                    <option value="Derajat 2" {{ old('specialist_data.periodontal.mobilitas', $sd['periodontal']['mobilitas'] ?? '') == 'Derajat 2' ? 'selected' : '' }}>Derajat 2</option>
                    <option value="Derajat 3" {{ old('specialist_data.periodontal.mobilitas', $sd['periodontal']['mobilitas'] ?? '') == 'Derajat 3' ? 'selected' : '' }}>Derajat 3</option>
                </select>
            </div>

            <div class="col-md-12 mt-3">
                <h6 class="text-muted small">Mukosa & Jaringan Lunak</h6>
            </div>
            <div class="col-md-4">
                <label class="form-label">Mukosa Oral</label>
                <textarea name="specialist_data[mukosa][oral]" class="form-control form-control-sm" rows="2" placeholder="Bibir, pipi, palatum, dasar mulut">{{ old('specialist_data.mukosa.oral', $sd['mukosa']['oral'] ?? '') }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Lidah</label>
                <textarea name="specialist_data[mukosa][lidah]" class="form-control form-control-sm" rows="2" placeholder="Dorsum, ventral, papil, ulkus">{{ old('specialist_data.mukosa.lidah', $sd['mukosa']['lidah'] ?? '') }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tonsil & Faring</label>
                <textarea name="specialist_data[mukosa][tonsil]" class="form-control form-control-sm" rows="2" placeholder="T1-T4, hiperemis, detritus">{{ old('specialist_data.mukosa.tonsil', $sd['mukosa']['tonsil'] ?? '') }}</textarea>
            </div>

            <div class="col-md-12 mt-3">
                <h6 class="text-muted small">Kebersihan Mulut</h6>
            </div>
            <div class="col-md-3">
                <label class="form-label">OHI-S (Oral Hygiene Index)</label>
                <select name="specialist_data[kebersihan][ohis]" class="form-select form-select-sm">
                    <option value="">--</option>
                    <option value="Baik (0-1.2)" {{ old('specialist_data.kebersihan.ohis', $sd['kebersihan']['ohis'] ?? '') == 'Baik (0-1.2)' ? 'selected' : '' }}>Baik (0-1.2)</option>
                    <option value="Sedang (1.3-3.0)" {{ old('specialist_data.kebersihan.ohis', $sd['kebersihan']['ohis'] ?? '') == 'Sedang (1.3-3.0)' ? 'selected' : '' }}>Sedang (1.3-3.0)</option>
                    <option value="Buruk (3.1-6.0)" {{ old('specialist_data.kebersihan.ohis', $sd['kebersihan']['ohis'] ?? '') == 'Buruk (3.1-6.0)' ? 'selected' : '' }}>Buruk (3.1-6.0)</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Kebiasaan Menyikat Gigi</label>
                <input type="text" name="specialist_data[kebersihan][menyikat]" class="form-control form-control-sm" value="{{ old('specialist_data.kebersihan.menyikat', $sd['kebersihan']['menyikat'] ?? '') }}" placeholder="Frekuensi, teknik">
            </div>
            <div class="col-md-3">
                <label class="form-label">Kebiasaan Merokok</label>
                <select name="specialist_data[kebersihan][merokok]" class="form-select form-select-sm">
                    <option value="">--</option>
                    <option value="Tidak" {{ old('specialist_data.kebersihan.merokok', $sd['kebersihan']['merokok'] ?? '') == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                    <option value="Kadang" {{ old('specialist_data.kebersihan.merokok', $sd['kebersihan']['merokok'] ?? '') == 'Kadang' ? 'selected' : '' }}>Kadang</option>
                    <option value="Perokok aktif" {{ old('specialist_data.kebersihan.merokok', $sd['kebersihan']['merokok'] ?? '') == 'Perokok aktif' ? 'selected' : '' }}>Perokok aktif</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">DMFT Index</label>
                <input type="text" name="specialist_data[kebersihan][dmft]" class="form-control form-control-sm" value="{{ old('specialist_data.kebersihan.dmft', $sd['kebersihan']['dmft'] ?? '') }}" placeholder="Decay/Missing/Filled">
            </div>

            <div class="col-md-12 mt-3">
                <h6 class="text-muted small">Tindakan</h6>
            </div>
            <div class="col-md-12">
                <label class="form-label">Rencana Tindakan</label>
                <textarea name="specialist_data[tindakan][rencana]" class="form-control" rows="2" placeholder="Tumpatan, scaling, ekstraksi, RCT, protesa...">{{ old('specialist_data.tindakan.rencana', $sd['tindakan']['rencana'] ?? '') }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Gigi yang Ditindak</label>
                <input type="text" name="specialist_data[tindakan][gigi]" class="form-control" value="{{ old('specialist_data.tindakan.gigi', $sd['tindakan']['gigi'] ?? '') }}" placeholder="e.g. 16, 26, 36">
            </div>
            <div class="col-md-6">
                <label class="form-label">Anestesi</label>
                <input type="text" name="specialist_data[tindakan][anestesi]" class="form-control" value="{{ old('specialist_data.tindakan.anestesi', $sd['tindakan']['anestesi'] ?? '') }}" placeholder="Lokal/regional, jenis">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleTooth(toothNum) {
    const input = document.getElementById('odontogramInput');
    const data = JSON.parse(input.value || '{}');
    const order = ['utuh', 'karies', 'tambalan', 'ekstraksi', 'mahkota'];
    const colors = {'utuh':'#28a745','karies':'#dc3545','tambalan':'#ffc107','ekstraksi':'#6c757d','mahkota':'#17a2b8'};
    const current = data[toothNum] || 'utuh';
    const nextIdx = (order.indexOf(current) + 1) % order.length;
    const next = order[nextIdx];
    data[toothNum] = next;
    input.value = JSON.stringify(data);
    const el = document.querySelector(`.odontogram-tooth[data-tooth="${toothNum}"]`);
    if (el) {
        el.dataset.status = next;
        el.style.backgroundColor = colors[next];
    }
}
</script>
@endpush