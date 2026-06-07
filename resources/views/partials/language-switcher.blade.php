@php($currentLocale = app()->getLocale())
@php($supportedLocales = config('app.supported_locales', []))

<div class="language-switcher" aria-label="{{ __('ui.language') }}">
    <i class="bi bi-translate"></i>
    @foreach($supportedLocales as $locale => $label)
        <a href="{{ route('locale.switch', $locale) }}" class="{{ $currentLocale === $locale ? 'active' : '' }}">
            {{ strtoupper($locale) }}
        </a>
    @endforeach
</div>
