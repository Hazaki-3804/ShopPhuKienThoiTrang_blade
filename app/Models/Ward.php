<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Ward extends Model
{
    use HasFactory;

    protected $table = 'wards';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'code_name',
        'province_id',
    ];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }
}
