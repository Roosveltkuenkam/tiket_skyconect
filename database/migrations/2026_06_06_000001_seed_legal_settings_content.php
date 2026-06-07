<?php

use App\Services\SettingManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeedLegalSettingsContent extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $this->updatePlaceholder('legal.terms', SettingManager::defaultTerms(), 'Conditions generales SkyConnect a completer.');
        $this->updatePlaceholder('legal.privacy', SettingManager::defaultPrivacy(), 'Politique de confidentialite SkyConnect a completer.');
    }

    public function down()
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $this->updateExact('legal.terms', SettingManager::defaultTerms(), 'Conditions generales SkyConnect a completer.');
        $this->updateExact('legal.privacy', SettingManager::defaultPrivacy(), 'Politique de confidentialite SkyConnect a completer.');
    }

    private function updatePlaceholder($key, $newValue, $placeholder)
    {
        $setting = DB::table('settings')->where('key', $key)->first();

        if (! $setting) {
            DB::table('settings')->insert([
                'key' => $key,
                'value' => $newValue,
                'type' => 'textarea',
                'setting_group' => 'legal',
                'is_secret' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return;
        }

        if ($setting->value === null || $setting->value === '' || $setting->value === $placeholder) {
            DB::table('settings')->where('key', $key)->update([
                'value' => $newValue,
                'type' => 'textarea',
                'setting_group' => 'legal',
                'is_secret' => false,
                'updated_at' => now(),
            ]);
        }
    }

    private function updateExact($key, $currentValue, $fallback)
    {
        DB::table('settings')
            ->where('key', $key)
            ->where('value', $currentValue)
            ->update([
                'value' => $fallback,
                'updated_at' => now(),
            ]);
    }
}
