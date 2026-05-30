<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'judul',
        'kategori',
        'deskripsi',
        'pelapor',
        'status',
        'is_anonim',
        'foto',
    ];

    protected $casts = [
        'is_anonim' => 'boolean',
    ];
}
