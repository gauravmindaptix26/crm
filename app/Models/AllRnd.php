<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllRnd extends Model
{
    protected $fillable = ['title', 'description', 'urls', 'department_id', 'attachment', 'created_by'];

    public function department()
{
    return $this->belongsTo(Department::class);
}

public function createdBy()
{
    return $this->belongsTo(User::class, 'created_by');
}

}
