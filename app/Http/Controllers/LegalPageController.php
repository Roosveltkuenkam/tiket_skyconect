<?php

namespace App\Http\Controllers;

use App\Services\SettingManager;

class LegalPageController extends Controller
{
    public function terms()
    {
        return view('legal.show', [
            'title' => 'Conditions generales',
            'content' => SettingManager::get('legal.terms', 'Conditions generales SkyConnect a completer.'),
        ]);
    }

    public function privacy()
    {
        return view('legal.show', [
            'title' => 'Politique de confidentialite',
            'content' => SettingManager::get('legal.privacy', 'Politique de confidentialite SkyConnect a completer.'),
        ]);
    }
}
