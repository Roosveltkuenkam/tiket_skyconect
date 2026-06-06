@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.profile.eyebrow') }}</span>
        <h3>{{ __('ui.profile.title') }}</h3>
        <p>{{ __('ui.profile.subtitle') }}</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <strong>{{ __('ui.profile.errors_title') }}</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="panel-card">
    <form method="POST" action="{{ route(($routeArea ?? 'admin') . '.profile.update') }}" class="row g-3">
        @csrf
        @method('PUT')

        <div class="col-md-6">
            <label class="form-label">{{ __('ui.profile.name') }}</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">{{ __('ui.profile.email') }}</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">{{ __('ui.profile.phone') }}</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone', auth()->user()->phone) }}">
        </div>

        <div class="col-md-6">
            <label class="form-label">{{ __('ui.profile.business_name') }}</label>
            <input type="text" name="business_name" class="form-control" value="{{ old('business_name', auth()->user()->business_name) }}">
        </div>

        <div class="col-md-6">
            <label class="form-label">{{ __('ui.profile.city') }}</label>
            <input type="text" name="city" class="form-control" value="{{ old('city', auth()->user()->city) }}">
        </div>

        <div class="col-md-6">
            <label class="form-label">{{ __('ui.profile.country') }}</label>
            <input type="text" name="country" class="form-control" value="{{ old('country', auth()->user()->country) }}">
        </div>

        <div class="col-12">
            <hr>
            <h4>{{ __('ui.profile.security') }}</h4>
            <p class="section-copy">{{ __('ui.profile.password_help') }}</p>
        </div>

        <div class="col-md-6">
            <label class="form-label">{{ __('ui.profile.new_password') }}</label>
            <input type="password" name="password" class="form-control" autocomplete="new-password">
        </div>

        <div class="col-md-6">
            <label class="form-label">{{ __('ui.profile.confirm_password') }}</label>
            <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
        </div>

        <div class="col-12 d-flex justify-content-end">
            <button class="sky-btn">{{ __('ui.profile.save') }}</button>
        </div>
    </form>
</div>
@endsection
