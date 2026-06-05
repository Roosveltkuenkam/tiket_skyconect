<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SettingManager
{
    public static function definitions()
    {
        return [
            'general' => [
                'label' => 'General',
                'settings' => [
                    'platform.name' => ['label' => 'Nom plateforme', 'type' => 'text', 'default' => 'SkyConnect'],
                    'platform.logo_path' => ['label' => 'Logo actuel', 'type' => 'file', 'default' => 'images/logo-skyconnect.PNG'],
                    'platform.currency' => ['label' => 'Devise', 'type' => 'text', 'default' => 'XAF'],
                    'platform.country' => ['label' => 'Pays', 'type' => 'text', 'default' => 'Cameroun'],
                ],
            ],
            'sales' => [
                'label' => 'Vente',
                'settings' => [
                    'sales.low_stock_threshold' => ['label' => 'Seuil stock faible par defaut', 'type' => 'number', 'default' => 10],
                    'sales.order_expiration_minutes' => ['label' => 'Expiration commande (minutes)', 'type' => 'number', 'default' => 15],
                    'sales.platform_fee_percent' => ['label' => 'Frais plateforme (%)', 'type' => 'number', 'default' => 0],
                ],
            ],
            'campay' => [
                'label' => 'Campay',
                'settings' => [
                    'campay.username' => ['label' => 'Campay username', 'type' => 'text', 'default' => null],
                    'campay.password' => ['label' => 'Campay password', 'type' => 'password', 'default' => null, 'secret' => true],
                    'campay.app_key' => ['label' => 'Campay app key', 'type' => 'password', 'default' => null, 'secret' => true],
                    'campay.base_url' => ['label' => 'Campay base URL', 'type' => 'text', 'default' => null],
                ],
            ],
            'mail' => [
                'label' => 'Mail SMTP',
                'settings' => [
                    'mail.host' => ['label' => 'SMTP host', 'type' => 'text', 'default' => null],
                    'mail.port' => ['label' => 'SMTP port', 'type' => 'number', 'default' => 587],
                    'mail.encryption' => ['label' => 'SMTP encryption', 'type' => 'text', 'default' => 'tls'],
                    'mail.username' => ['label' => 'SMTP username', 'type' => 'text', 'default' => null],
                    'mail.password' => ['label' => 'SMTP password', 'type' => 'password', 'default' => null, 'secret' => true],
                    'mail.from_address' => ['label' => 'Email expediteur', 'type' => 'text', 'default' => 'hello@skyconnect.local'],
                    'mail.from_name' => ['label' => 'Nom expediteur', 'type' => 'text', 'default' => 'SkyConnect'],
                ],
            ],
            'legal' => [
                'label' => 'Legal',
                'settings' => [
                    'legal.terms' => ['label' => 'Conditions generales', 'type' => 'textarea', 'default' => 'Conditions generales SkyConnect a completer.'],
                    'legal.privacy' => ['label' => 'Politique de confidentialite', 'type' => 'textarea', 'default' => 'Politique de confidentialite SkyConnect a completer.'],
                ],
            ],
        ];
    }

    public static function ensureDefaults()
    {
        foreach (self::definitions() as $group => $definitionGroup) {
            foreach ($definitionGroup['settings'] as $key => $definition) {
                Setting::firstOrCreate(
                    ['key' => $key],
                    [
                        'value' => self::encodeValue($definition['default'] ?? null, $definition['secret'] ?? false),
                        'type' => $definition['type'],
                        'setting_group' => $group,
                        'is_secret' => $definition['secret'] ?? false,
                    ]
                );
            }
        }
    }

    public static function get($key, $default = null)
    {
        try {
            if (! Schema::hasTable('settings')) {
                return $default;
            }

            $setting = Setting::where('key', $key)->first();
        } catch (Throwable $exception) {
            return $default;
        }

        if (! $setting) {
            return $default;
        }

        return self::decodeValue($setting->value, $setting->is_secret) ?? $default;
    }

    public static function set($key, $value, array $definition)
    {
        $isSecret = $definition['secret'] ?? false;

        return Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => self::encodeValue($value, $isSecret),
                'type' => $definition['type'],
                'setting_group' => self::groupFor($key),
                'is_secret' => $isSecret,
            ]
        );
    }

    public static function displayValue(Setting $setting)
    {
        if ($setting->is_secret) {
            return $setting->value ? '********' : '';
        }

        return self::decodeValue($setting->value, false);
    }

    public static function applyRuntimeConfig()
    {
        if (! self::tableReady()) {
            return;
        }

        config([
            'app.name' => self::get('platform.name', config('app.name')),
            'mail.mailers.smtp.host' => self::get('mail.host', config('mail.mailers.smtp.host')),
            'mail.mailers.smtp.port' => self::get('mail.port', config('mail.mailers.smtp.port')),
            'mail.mailers.smtp.encryption' => self::get('mail.encryption', config('mail.mailers.smtp.encryption')),
            'mail.mailers.smtp.username' => self::get('mail.username', config('mail.mailers.smtp.username')),
            'mail.mailers.smtp.password' => self::get('mail.password', config('mail.mailers.smtp.password')),
            'mail.from.address' => self::get('mail.from_address', config('mail.from.address')),
            'mail.from.name' => self::get('mail.from_name', config('mail.from.name')),
            'services.campay.username' => self::get('campay.username'),
            'services.campay.password' => self::get('campay.password'),
            'services.campay.app_key' => self::get('campay.app_key'),
            'services.campay.base_url' => self::get('campay.base_url'),
        ]);
    }

    private static function encodeValue($value, $secret)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $secret ? Crypt::encryptString((string) $value) : (string) $value;
    }

    private static function decodeValue($value, $secret)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! $secret) {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (Throwable $exception) {
            return null;
        }
    }

    private static function groupFor($key)
    {
        foreach (self::definitions() as $group => $definitionGroup) {
            if (array_key_exists($key, $definitionGroup['settings'])) {
                return $group;
            }
        }

        return 'general';
    }

    private static function tableReady()
    {
        try {
            return Schema::hasTable('settings');
        } catch (Throwable $exception) {
            return false;
        }
    }
}
