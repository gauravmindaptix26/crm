<?php

namespace App\Models;
use App\Models\User;
use App\Models\SupportTicket;



use Illuminate\Database\Eloquent\Model;

class SupportTicketReply extends Model
{

    protected $fillable = [
        'support_ticket_id',
        'user_id',
        'message',
    ];

    public function ticket()
{
    return $this->belongsTo(SupportTicket::class);
}

public function user()
{
    return $this->belongsTo(User::class);
}
}
