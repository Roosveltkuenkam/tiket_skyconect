<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function set(Request $request, $locale)
    {
        $supportedLocales = array_keys(config('app.supported_locales', []));

        abort_unless(in_array($locale, $supportedLocales, true), 404);

        $request->session()->put('locale', $locale);

        return back();
    }
}
