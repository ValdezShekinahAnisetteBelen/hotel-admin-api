<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['name', 'description', 'amenities', 'price', 'images', 'status'];

    protected $casts = [
        'amenities' => 'array',
        'images' => 'array',
    ];
}


