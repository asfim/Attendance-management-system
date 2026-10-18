<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function companyName(string $default = 'Attendify Technologies Ltd.'): string
    {
        $name = self::get('company_name');
        if (!empty($name)) {
            return $name;
        }
        $legacy = self::get('school_name');
        if (!empty($legacy) && !str_contains(strtolower($legacy), 'school') && !str_contains(strtolower($legacy), 'college')) {
            return $legacy;
        }
        return config('app.name', $default);
    }

    public static function companyAddress(string $default = 'Dhaka, Bangladesh'): string
    {
        return self::get('company_location')
            ?: self::get('school_address_short')
            ?: self::get('school_address', $default);
    }

    public static function companyRegNo(string $default = 'TR-2026-9812'): string
    {
        return self::get('company_reg_no')
            ?: self::get('school_eiin', $default);
    }

    public static function companyLogo(): ?string
    {
        return self::get('site_logo');
    }

    public static function companyFavicon(): ?string
    {
        return self::get('site_favicon') ?: self::companyLogo();
    }
}
