<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $fillable = [
        'key_name',
        'value',
    ];

    public static function get($key)
    {
        return self::where('key_name', $key)->value('value');
    }

    public static function set($key, $value)
    {
        return self::updateOrCreate(['key_name' => $key], ['value' => $value]);
    }
}
