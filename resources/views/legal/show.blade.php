@extends('layouts.public')

@section('title', $title . ' - ' . \App\Services\SettingManager::get('platform.name', 'SkyConnect'))

@section('content')
<main class="sky-section">
    <div class="sky-container">
        <div class="page-header">
            <span class="eyebrow"><span class="eyebrow-dot"></span> Legal</span>
            <h3>{{ $title }}</h3>
            <p>{{ \App\Services\SettingManager::get('platform.name', 'SkyConnect') }}</p>
        </div>

        <div class="panel-card">
            <div style="white-space:pre-wrap;line-height:1.8;color:#2d3748;">{{ $content }}</div>
        </div>
    </div>
</main>
@endsection
