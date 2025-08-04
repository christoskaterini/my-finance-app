<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public $timestamps = false;

    public static function get(string $key, $default = null)
    {
        if ($setting = self::find($key)) {
            return $setting->value;
        }

        return $default;
    }

    public static function set(string $key, $value = null): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}