<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\ActivityLogger;
use App\Services\SettingManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AdminSettingController extends Controller
{
    public function index()
    {
        SettingManager::ensureDefaults();

        $settings = Setting::orderBy('setting_group')->orderBy('key')->get()->keyBy('key');

        return view('admin.settings.index', [
            'definitions' => SettingManager::definitions(),
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        SettingManager::ensureDefaults();

        $request->validate([
            'settings' => 'nullable|array',
            'settings.*' => 'nullable',
            'logo' => 'nullable|image|max:2048',
        ]);

        $changes = [];
        $submitted = $request->input('settings', []);

        foreach (SettingManager::definitions() as $group) {
            foreach ($group['settings'] as $key => $definition) {
                if (($definition['type'] ?? null) === 'file') {
                    continue;
                }

                $isSecret = $definition['secret'] ?? false;
                $value = $submitted[$key] ?? null;

                if ($isSecret && ($value === null || $value === '')) {
                    continue;
                }

                $old = SettingManager::get($key);
                SettingManager::set($key, $value, $definition);
                $new = SettingManager::get($key);

                if ($old !== $new) {
                    $changes[$key] = $isSecret
                        ? ['old' => '[hidden]', 'new' => '[hidden]']
                        : ['old' => $old, 'new' => $new];
                }
            }
        }

        if ($request->hasFile('logo')) {
            File::ensureDirectoryExists(public_path('settings'));
            $fileName = 'logo-' . Str::random(10) . '.' . $request->file('logo')->getClientOriginalExtension();
            $request->file('logo')->move(public_path('settings'), $fileName);
            $old = SettingManager::get('platform.logo_path');

            SettingManager::set('platform.logo_path', 'settings/' . $fileName, [
                'type' => 'file',
                'secret' => false,
            ]);

            $changes['platform.logo_path'] = ['old' => $old, 'new' => 'settings/' . $fileName];
        }

        if ($changes) {
            ActivityLogger::log('settings.updated', Setting::class, [
                'changes' => $changes,
            ], $request);
        }

        SettingManager::applyRuntimeConfig();

        return back()->with('success', 'Parametres mis a jour.');
    }
}
