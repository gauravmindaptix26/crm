<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeReview extends Model
{
    protected $fillable = [
        'employee_id',
        'project_manager_id',
        'department_id',
        'review_month',
        'quality_of_work',
        'communication',
        'ownership',
        'team_collaboration',
        'overall_rating',
        'comments',
    ];

    protected $casts = [
        'review_month' => 'date',
        'overall_rating' => 'float',
    ];

    // 🔹 Employee who was reviewed
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    // 🔹 Project Manager (Reviewer)
    public function projectManager()
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    // 🔹 Department relation
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    // 🔹 Compute overall rating dynamically
    public static function computeOverall(array $scores): float
    {
        $sum = array_sum($scores);
        $count = count($scores);
        return $count ? round($sum / $count, 2) : 0.0;
    }
    public function reviewer()
{
    return $this->belongsTo(\App\Models\User::class, 'project_manager_id');
}
public function getOverallRatingAttribute()
{
    $fields = [
        $this->communication,
        $this->teamwork,
        $this->punctuality,
        $this->leadership,
    ];

    $filled = array_filter($fields, fn($val) => $val !== null);
    return count($filled) ? round(array_sum($filled) / count($filled), 1) : null;
}
}
