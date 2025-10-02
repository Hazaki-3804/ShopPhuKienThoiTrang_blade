<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    //
    protected $table = 'banners';

    protected $fillable = ['name', 'image_url', 'status', 'type'];

    public function getImageUrlAttribute($value)
    {
        if (!$value) return null;
        return \Illuminate\Support\Str::startsWith($value, ['http://', 'https://', '/']) ? $value : asset($value);
    }
}
