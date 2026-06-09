@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Master Tes Laboratorium</h4>
        <a href="{{ route('lab-tests.create') }}" class="btn btn-primary">+ Tambah Tes</a>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Cari</label>
                    <input type="text" name="search" class="form-control" placeholder="Kode atau nama tes..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-select">
                        <option value="">Semua</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">Cari</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('lab-tests.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Tes</th>
                        <th>Kategori</th>
                        <th>Spesimen</th>
                        <th>Satuan</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th width="140">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tests as $test)
                        <tr>
                            <td>{{ $test->code }}</td>
                            <td>{{ $test->name }}</td>
                            <td>{{ $test->category->name ?? '-' }}</td>
                            <td>{{ $test->specimen_type ?? '-' }}</td>
                            <td>{{ $test->unit ?? '-' }}</td>
                            <td>Rp {{ number_format($test->price, 0, ',', '.') }}</td>
                            <td>
                                @if($test->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('lab-tests.show', $test) }}" class="btn btn-sm btn-info">Detail</a>
                                <a href="{{ route('lab-tests.edit', $test) }}" class="btn btn-sm btn-warning">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Tidak ada tes laboratorium</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $tests->links() }}
        </div>
    </div>
</div>
@endsection
