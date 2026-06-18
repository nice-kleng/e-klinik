@php $files = $attachments ?? $medicalRecord->attachments ?? collect(); @endphp

{{-- Daftar Berkas --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-paperclip me-1 text-primary"></i>Berkas</h6>
        @if(!isset($hideUpload))
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="fas fa-upload me-1"></i>Upload
            </button>
        @endif
    </div>
    <div class="card-body p-0">
        @if($files->count())
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama File</th>
                            <th>Kategori</th>
                            <th>Ukuran</th>
                            <th>Tgl Upload</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($files as $file)
                            <tr>
                                <td>
                                    <i class="fas {{ match($file->file_type) { 'image/jpeg','image/png','image/gif' => 'fa-image text-success', 'application/pdf' => 'fa-file-pdf text-danger', default => 'fa-file text-muted' } }} me-1"></i>
                                    {{ $file->file_name }}
                                </td>
                                <td>
                                    @php
                                        $catLabel = match($file->category) { 'lab_result' => 'Hasil Lab', 'xray' => 'Radiologi', 'photo' => 'Foto', default => 'Lainnya' };
                                        $catBadge = match($file->category) { 'lab_result' => 'bg-info', 'xray' => 'bg-warning', 'photo' => 'bg-success', default => 'bg-secondary' };
                                    @endphp
                                    <span class="badge {{ $catBadge }}">{{ $catLabel }}</span>
                                </td>
                                <td>
                                    @if($file->file_size > 1048576)
                                        {{ round($file->file_size / 1048576, 1) }} MB
                                    @elseif($file->file_size > 1024)
                                        {{ round($file->file_size / 1024, 1) }} KB
                                    @else
                                        {{ $file->file_size ?? '-' }} B
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $file->created_at?->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('attachments.download', $file) }}" class="btn btn-sm btn-outline-info" title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <form method="POST" action="{{ route('attachments.destroy', $file) }}" onsubmit="return confirm('Hapus berkas ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center text-muted py-4">
                <i class="fas fa-paperclip fa-2x mb-2 d-block"></i>
                Belum ada berkas diunggah
            </div>
        @endif
    </div>
</div>

{{-- Modal Upload --}}
@if(!isset($hideUpload))
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('attachments.store', $medicalRecord) }}" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Upload Berkas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">File <span class="text-danger">*</span></label>
                    <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" required>
                    <small class="text-muted">Format: JPG, PNG, GIF, PDF, DOC, DOCX. Maks 10 MB.</small>
                    @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <select name="category" class="form-select">
                        <option value="other">Lainnya</option>
                        <option value="lab_result">Hasil Lab</option>
                        <option value="photo">Foto</option>
                        <option value="xray">Radiologi</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Upload</button>
            </div>
        </form>
    </div>
</div>
@endif