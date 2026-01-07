<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DsrReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'work_description',
        'hours_spent'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}