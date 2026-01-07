<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Project;


class HiredFrom extends Model
{
    protected $table = 'hired_froms'; // explicitly set plural table name
    protected $fillable = ['name', 'description'];

    public function projects()
{
    return $this->hasMany(Project::class, 'hired_from_id');
}
public function hiredFromProfile()
    {
        return $this->belongsTo(HiredFrom::class, 'hired_from_profile_id', 'id');
    }
}
