@extends('layouts.volt')

@section('title', 'Satu Sehat - Organization')

@section('header', 'Organization')

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if ($error)
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-1"></i> {{ $error }}
                </div>
            @endif

            @if ($orgData && $orgData['valid'])
                <div class="row g-3">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td class="text-muted" style="width: 160px">ID Organisasi</td>
                                <td><code>{{ $orgData['organization']['id'] }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Nama</td>
                                <td><strong>{{ $orgData['organization']['name'] }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tipe</td>
                                <td>{{ $orgData['organization']['type'] }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status</td>
                                <td>
                                    @if ($orgData['organization']['active'])
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Tidak Aktif</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle me-1"></i> Organisasi terverifikasi di Satu Sehat
                        </div>
                    </div>
                </div>
            @elseif ($orgData && !$orgData['valid'])
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-1"></i> {{ $orgData['message'] ?? 'Organisasi tidak valid' }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
