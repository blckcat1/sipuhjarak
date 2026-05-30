<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $fillable = [
        'type',
        'title',
        'date',
        'tag',
        'color',
        'img',
        'description',
    ];
}
