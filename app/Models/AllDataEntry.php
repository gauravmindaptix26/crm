<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllDataEntry extends Model
{
    protected $fillable = [
        'name', 'description', 'email', 'phone_number', 'data_option', 'created_by'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
