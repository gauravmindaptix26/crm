<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SalesLead;
use App\Models\User;



class SalesLeadNote extends Model
{

    protected $fillable = [
        'sales_lead_id',     // reference to sales lead
        'note_type',         // follow_up or general
        'title',             // note title
        'description',       // note body
        'attachment',        // file path if any
        'created_by',        // user who added the note
    ];

    public function lead()
{
    return $this->belongsTo(SalesLead::class);
}

public function user()
{
    return $this->belongsTo(User::class, 'added_by');
}

}
