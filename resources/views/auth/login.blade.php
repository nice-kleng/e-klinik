@extends('layouts.volt-guest')

@section('header', 'Masuk ke e-Klinik')

@section('content')
<form method="POST" action="{{ route('login') }}" class="mt-4">
    @csrf

    <div class="form-group mb-4">
        <label for="email">Email</label>
        <div class="input-group">
            <span class="input-group-text" id="basic-addon1">
                <i class="fas fa-envelope text-gray-600"></i>
            </span>
            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="admin@e-klinik.com" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="form-group mb-4">
        <label for="password">Password</label>
        <div class="input-group">
            <span class="input-group-text" id="basic-addon2">
                <i class="fas fa-lock text-gray-600"></i>
            </span>
            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Password" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Ingat saya</label>
        </div>
        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="small text-decoration-none">Lupa password?</a>
        @endif
    </div>

    <div class="d-grid">
        <button type="submit" class="btn btn-gray-800">Masuk</button>
    </div>
</form>
@endsection
