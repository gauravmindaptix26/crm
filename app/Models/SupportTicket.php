<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class SupportTicket extends Model
{
    protected $fillable = [
        'title', 'description', 'priority', 'user_id', 'assigned_to', 'status'
    ];
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies()
    {
        return $this->hasMany(SupportTicketReply::class);
    }
    public function assignedUser()
{
    return $this->belongsTo(User::class, 'assigned_to');
}
public function assignedTo()
{
    return $this->belongsTo(User::class, 'assigned_to');
}
public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}
}
