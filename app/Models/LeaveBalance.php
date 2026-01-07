<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model {
    use HasFactory;

    protected $fillable = ['user_id', 'leave_policy_id', 'balance', 'last_renewed_at'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function leavePolicy() {
        return $this->belongsTo(LeavePolicy::class);
    }
}