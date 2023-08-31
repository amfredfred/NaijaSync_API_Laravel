<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSettings extends Model {
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    public static function getSetting( $key, $defaultValue = null ) {
        $setting = self::where( 'key', $key )->first();

        return $setting ? $setting->value : $defaultValue;
    }
}