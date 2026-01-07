<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;


class Dsr extends Model
{

    protected $table = 'dsrs';

    protected $fillable = [
        'user_id', 'project_id', 'work_description', 'hours', 'helped_by', 
        'help_description', 'help_rating', 'replied_to_emails', 
        'sent_report', 'justified_work'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function project() {
        return $this->belongsTo(Project::class);
    }

   


    public function helper() {
        return $this->belongsTo(User::class, 'helped_by');
    }
    public function tasks()
{
    return $this->hasMany(Dsr::class, 'dsr_id');
}
public function helpedBy()
    {
        return $this->belongsTo(User::class, 'helped_by');
    }
}
