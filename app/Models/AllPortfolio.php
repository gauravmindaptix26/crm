<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllPortfolio extends Model
{
    protected $fillable = [
        'title',
        'country_id',
        'department_id',
        'description',
        'attachment',
        'created_by',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
