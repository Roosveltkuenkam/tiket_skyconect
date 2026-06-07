<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpFoundation\Response;

class TranslateStaticHtml
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (! $this->shouldTranslate($response)) {
            return $response;
        }

        $translations = Lang::get('static');

        if (! is_array($translations) || empty($translations)) {
            return $response;
        }

        $content = $response->getContent();
        $response->setContent(strtr($content, $translations));

        return $response;
    }

    private function shouldTranslate($response)
    {
        if (! $response instanceof Response || ! method_exists($response, 'getContent')) {
            return false;
        }

        if (App::getLocale() === config('app.fallback_locale', 'fr')) {
            return false;
        }

        return str_contains((string) $response->headers->get('Content-Type'), 'text/html');
    }
}
