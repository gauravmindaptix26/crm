<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Niche extends Model
{
    protected $fillable = ['name', 'added_by'];

    public function user()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
