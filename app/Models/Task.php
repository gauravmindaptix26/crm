<?php

namespace App\Models;
use App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['name', 'description','done_message', 'created_by'];

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'task_user_days')
                    ->withPivot('days')
                    ->withTimestamps();
    }
    
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    

}