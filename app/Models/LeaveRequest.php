<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LeaveRequest extends Model {
    use HasFactory;

    protected $fillable = [
        'user_id', 'leave_policy_id', 'start_date', 'end_date', 'duration',
        'start_half', 'end_half', 'partial_type', 'partial_minutes',
        'is_partial', 'note', 'status','reason', 'approved_by', 'approved_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'duration' => 'decimal:2',
        'partial_minutes' => 'integer',
        'is_partial' => 'boolean',
        'approved_at' => 'datetime'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function leavePolicy() {
        return $this->belongsTo(LeavePolicy::class);
    }

    public function approver() {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getDurationDisplayAttribute() {
        if ($this->is_partial) {
            return $this->partial_minutes . ' min (' . number_format($this->duration, 2) . ' days)';
        }
        if ($this->start_half || $this->end_half) {
            return number_format($this->duration, 2) . ' days (Partial)';
        }
        return number_format($this->duration, 2) . ' days';
    }

    public function getDateRangeAttribute() {
        if ($this->end_date && $this->end_date->gt($this->start_date)) {
            return $this->start_date->format('d M Y') . ' to ' . $this->end_date->format('d M Y');
        }
        return $this->start_date->format('d M Y');
    }
}