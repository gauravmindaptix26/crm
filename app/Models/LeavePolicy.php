<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeavePolicy extends Model {
    use HasFactory;

    protected $fillable = ['name', 'days_per_quarter', 'probation_months'];

    public function balances() {
        return $this->hasMany(LeaveBalance::class);
    }
    public function leaveBalances() {
        return $this->hasMany(LeaveBalance::class);
    }
}