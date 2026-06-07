<?php

namespace App\Http\Controllers;

use App\Services\SettingManager;

class LegalPageController extends Controller
{
    public function terms()
    {
        return view('legal.show', [
            'title' => __('ui.legal.terms_title'),
            'content' => SettingManager::get('legal.terms', SettingManager::defaultTerms()),
        ]);
    }

    public function privacy()
    {
        return view('legal.show', [
            'title' => __('ui.legal.privacy_title'),
            'content' => SettingManager::get('legal.privacy', SettingManager::defaultPrivacy()),
        ]);
    }
}
