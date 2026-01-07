<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class UserNote extends Model
{
    protected $fillable = [
        'user_id', 'added_by', 'title', 'description', 'note_type', 'rating'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function addedBy() {
        return $this->belongsTo(User::class, 'added_by');
    }
}
