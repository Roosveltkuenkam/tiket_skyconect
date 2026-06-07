@extends('layouts.admin')

@section('content')

<div class="page-header">
    <h3>{{ __('ui.dashboard_pages.settings_title') }}</h3>
    <p>{{ __('ui.dashboard_pages.settings_subtitle') }}</p>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="panel-card">
    <form method="POST" action="{{ route('dashboard.settings.update') }}">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ __('ui.dashboard_pages.business_name') }}</label>
                <input type="text" name="business_name" class="form-control" value="{{ old('business_name', auth()->user()->business_name) }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ __('ui.auth.phone') }}</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', auth()->user()->phone) }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ __('ui.auth.city') }}</label>
                <input type="text" name="city" class="form-control" value="{{ old('city', auth()->user()->city) }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ __('ui.auth.country') }}</label>
                <input type="text" name="country" class="form-control" value="{{ old('country', auth()->user()->country) }}">
            </div>
        </div>

        <button type="submit" class="sky-btn">{{ __('ui.dashboard_pages.save') }}</button>
    </form>
</div>

@endsection
