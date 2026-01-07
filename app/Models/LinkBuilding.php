<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkBuilding extends Model {
    use HasFactory;

    protected $fillable = ['website', 'pa', 'da', 'niche', 'countries', 'type_of_link'];

    protected $casts = [
        'niche' => 'array',
        'countries' => 'array',
    ];
}
