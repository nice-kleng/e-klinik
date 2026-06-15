@extends('layouts.volt-guest')

@section('content')
    <div class="mb-4 text-muted small">
        Lupa kata sandi? Tidak masalah. Beri tahu kami alamat email Anda dan kami akan mengirimkan tautan pengaturan ulang kata sandi yang memungkinkan Anda memilih yang baru.
    </div>

    @if (session('status'))
        <div class="alert alert-success mb-4">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-end mt-3">
            <button type="submit" class="btn btn-primary">
                Kirim Tautan Reset Password
            </button>
        </div>
    </form>
@endsection
