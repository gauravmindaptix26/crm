<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailNotification extends Model
{
    protected $fillable = [
        'subject', 'content', 'sent_by', 'recipients',
    ];

    protected $casts = [
        'recipients' => 'array',
    ];
}
