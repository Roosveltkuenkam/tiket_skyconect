@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Configuration</span>
        <h3>Parametres globaux</h3>
        <p>Configurez SkyConnect sans modifier le code. Les secrets sont chiffres et masques.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-4">
        @foreach($definitions as $groupKey => $group)
            <div class="col-lg-6">
                <div class="panel-card h-100">
                    <span class="eyebrow"><span class="eyebrow-dot"></span> {{ $group['label'] }}</span>

                    <div class="row g-3 mt-2">
                        @foreach($group['settings'] as $key => $definition)
                            @php($setting = $settings[$key] ?? null)
                            @php($oldSettings = old('settings', []))
                            @php($value = array_key_exists($key, $oldSettings) ? $oldSettings[$key] : ($setting ? \App\Services\SettingManager::displayValue($setting) : ($definition['default'] ?? '')))

                            <div class="col-12">
                                <label class="form-label">{{ $definition['label'] }}</label>

                                @if($definition['type'] === 'textarea')
                                    <textarea name="settings[{{ $key }}]" class="form-control" rows="6">{{ $value }}</textarea>
                                @elseif($definition['type'] === 'password')
                                    <input type="password" name="settings[{{ $key }}]" class="form-control" placeholder="{{ $setting && $setting->value ? 'Valeur deja configuree' : '' }}">
                                    <small class="text-muted">Laissez vide pour conserver la valeur actuelle.</small>
                                @elseif($definition['type'] === 'file')
                                    @if($value)
                                        <div class="mb-2">
                                            <img src="{{ asset($value) }}" alt="Logo actuel" class="logo-tile" style="max-width:190px;border-radius:12px;padding:8px;">
                                        </div>
                                    @endif
                                    <input type="file" name="logo" class="form-control" accept="image/*">
                                    <small class="text-muted">PNG/JPG, 2 Mo maximum.</small>
                                @elseif($definition['type'] === 'checkbox')
                                    <input type="hidden" name="settings[{{ $key }}]" value="0">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="settings[{{ $key }}]" value="1" class="form-check-input" id="setting-{{ \Illuminate\Support\Str::slug($key) }}" {{ (string) $value === '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="setting-{{ \Illuminate\Support\Str::slug($key) }}">Active</label>
                                    </div>
                                @else
                                    <input type="{{ $definition['type'] === 'number' ? 'number' : 'text' }}" name="settings[{{ $key }}]" class="form-control" value="{{ $value }}">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="panel-card mt-4 d-flex justify-content-between align-items-center">
        <div>
            <strong>Enregistrer la configuration</strong>
            <div class="text-muted">Les changements sensibles seront audites.</div>
        </div>
        <button class="sky-btn">Enregistrer</button>
    </div>
</form>
@endsection
