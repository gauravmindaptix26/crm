<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrNote extends Model
{
    protected $fillable = [
        'user_id', 'added_by', 'title', 'note_type', 'timing', 'behaviour',
        'no_of_fine', 'no_of_applications', 'rating', 'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
