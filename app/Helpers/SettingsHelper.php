<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        try {
            $settings = Cache::rememberForever('global_settings', function () {
                return Setting::all()->pluck('value', 'key');
            });
            
            return data_get($settings, $key, $default);
        } catch (\Exception $e) {
            return $default;
        }
    }
}

if (!function_exists('seo')) {
    function seo(): \App\Services\SeoService
    {
        return app(\App\Services\SeoService::class);
    }
}
