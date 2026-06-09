@extends('layouts.app')

@section('header', 'Profil')

@section('content')
<div class="row">
    <div class="col-lg-6">
        @include('profile.partials.update-profile-information-form')
    </div>
    <div class="col-lg-6">
        @include('profile.partials.update-password-form')
        <div class="mt-4">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</div>
@endsection
