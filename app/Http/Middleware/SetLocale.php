<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $supportedLocales = array_keys(config('app.supported_locales', ['fr' => 'Francais']));
        $locale = Session::get('locale', config('app.locale', 'fr'));

        if (! in_array($locale, $supportedLocales, true)) {
            $locale = config('app.locale', 'fr');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
