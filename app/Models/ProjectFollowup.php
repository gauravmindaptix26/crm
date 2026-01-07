<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectFollowup extends Model
{
    use HasFactory;
    protected $fillable = ['project_id', 'user_id', 'subject', 'message', 'client_email', 'sent_at'];

    protected $dates = ['sent_at'];

    public function project() {
        return $this->belongsTo(Project::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
    
}
