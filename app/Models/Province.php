<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Province extends Model
{
    use HasFactory;

    protected $table = 'provinces';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'code_name',
    ];

    public function wards()
    {
        return $this->hasMany(Ward::class);
    }
}
