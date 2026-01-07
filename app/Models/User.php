<?php

namespace App\Models;
use Spatie\Permission\Traits\HasRoles;
use App\Models\HrNote;


// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Department;
use App\Models\UserNote;
use App\Models\SeoPmDsr;

use Carbon\Carbon;



class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
       'name', 'email', 'password', 'phone_number', 'monthly_target', 'upsell_incentive',
        'role', 'reporting_person', 'department_id', 'allow_view_all_projects',
        'disable_login', 'image', 'experience', 'qualification', 'specialization',
        'date_of_joining', 'employee_code','monthly_salary','allow_all_projects',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function department()
{
    return $this->belongsTo(Department::class, 'department_id');
}

public function reportingPerson()
{
    return $this->belongsTo(User::class, 'reporting_person');
}
public function projects()
{
    return $this->hasMany(Project::class,'assign_main_employee_id'); // Adjust this as per your database relationship
}
public function saleTeamProjects()
{
    return $this->hasMany(SaleTeamProject::class, 'sales_person_id');
}
public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}
public function assignedProjects()
{
    return $this->belongsToMany(Project::class);
}
public function dsrs()
{
    return $this->hasMany(Dsr::class, 'user_id'); // or correct foreign key
}

public function tasks()
{
    return $this->belongsToMany(Task::class, 'task_user_days')
                ->withPivot('days')
                ->withTimestamps();
}
public function userNotes()
{
    return $this->hasMany(UserNote::class);
}
public function hrNotes()
{
    return $this->hasMany(HrNote::class);
}
// Accessor for calculated experience
public function getCompanyExperienceAttribute($value)
{
    if ($this->date_of_joining) {
        $start = \Carbon\Carbon::parse($this->date_of_joining);
        $now = \Carbon\Carbon::now();

        $diff = $start->diff($now);

        $years = $diff->y;
        $months = $diff->m;
        $days = $diff->d;

        if ($years > 0) {
            return "{$years} year(s) {$months} month(s)";
        } elseif ($months > 0) {
            return "{$months} month(s) {$days} day(s)";
        } else {
            return "{$days} day(s)";
        }
    }

    // fallback if no joining date
    return $value ?? 'N/A';
}
public function getLeaveBalance($policyName)
{
    $policy = LeavePolicy::where('name', $policyName)->first();
    if (!$policy) {
        return [
            'available' => 0,
            'consumed' => 0,
            'quota' => 0,
            'accrued' => 0
        ];
    }

    $quota = $policy->name === 'Unpaid Leave' ? '∞' : ($policy->days_per_quarter ?? 0);
    
    // Probation check
    $isEligible = $this->isEligibleForPolicy($policy);
    if (!$isEligible && $policy->name !== 'Unpaid Leave') {
        return [
            'available' => 0,
            'consumed' => 0,
            'quota' => $quota,
            'accrued' => 0
        ];
    }

    // Calculate consumed for current quarter
    $now = now();
    $quarterStart = $now->startOfQuarter();
    $consumed = $this->leaveRequests()
        ->where('leave_policy_id', $policy->id)
        ->where('status', 'approved')
        ->where('start_date', '>=', $quarterStart)
        ->sum('duration');

    $available = $policy->name === 'Unpaid Leave' ? '∞' : max(0, $quota - $consumed);
    $accrued = $policy->name === 'Unpaid Leave' ? '∞' : ($quota); // Adjust if accrual logic differs

    return [
        'available' => $available,
        'consumed' => number_format($consumed, 1),
        'quota' => $quota,
        'accrued' => $accrued
    ];
}
private function calculateConsumedForPolicy($policy) {
    $now = now();
    $quarterStart = $now->copy()->startOfQuarter();
    
    return $this->leaveRequests()
        ->where('leave_policy_id', $policy->id)
        ->where('status', 'approved')
        ->where('start_date', '>=', $quarterStart)
        ->sum('duration');
}

public function isEligibleForPolicy($policy) {
    // Unpaid Leave is always eligible
    if ($policy->name === 'Unpaid Leave') {
        return true;
    }
    
    // If no date_of_joining, not eligible for paid leaves
    if (!$this->date_of_joining) {
        return false;
    }
    
    // Fix: Use proper date comparison with absolute months
    $joinDate = Carbon::parse($this->date_of_joining);
    $currentDate = now();
    
    // Ensure join date is in the past
    if ($joinDate->isFuture()) {
        return false;
    }
    
    // Calculate months difference properly
    $monthsDiff = $currentDate->diffInMonths($joinDate, false);
    
    // If negative (which shouldn't happen), use absolute value or fallback to years
    $monthsSinceJoining = abs($monthsDiff);
    
    // Additional check: if more than 1 year, definitely eligible
    if ($currentDate->diffInYears($joinDate) >= 1) {
        return true;
    }
    
    return $monthsSinceJoining >= $policy->probation_months;
}

public function leaveRequests() {
    return $this->hasMany(LeaveRequest::class, 'user_id');
}



// Add this for debugging
public function getProbationStatus($policy) {
    if ($policy->name === 'Unpaid Leave') return 'Eligible';
    
    if (!$this->date_of_joining) return 'No join date';
    
    $joinDate = Carbon::parse($this->date_of_joining);
    $months = now()->diffInMonths($joinDate, false);
    $required = $policy->probation_months;
    
    return $months >= $required ? 'Eligible' : "Probation: {$months}/{$required} months";
}
public function manager() {
    return $this->belongsTo(User::class, 'reporting_person');
}
public function team() {
    return $this->hasMany(User::class, 'reporting_person');
}
public function canApproveLeave($leaveRequest)
    {
        // HR and Admin can approve any request
        if ($this->hasAnyRole(['HR', 'Admin'])) {
            return true;
        }
        
        // Project Manager/Team Lead can approve their team's requests
        if ($this->hasAnyRole(['Project Manager', 'Team Lead'])) {
            $teamUserIds = $this->team()->pluck('id')->toArray();
            return in_array($leaveRequest->user_id, $teamUserIds);
        }
        
        return false;
    }
    public function weeklyDsrs()
{
    return $this->hasMany(SeoPmDsr::class, 'pm_id')->where('type', 'weekly');
}
public function monthlyDsrs()
{
    return $this->hasMany(SeoPmDsr::class, 'pm_id')->where('type', 'monthly');
}
public function seoPmDsrs()
{
    return $this->hasMany(SeoPmDsr::class, 'pm_id');
}

public function latestDailyDsr()
{
    return $this->hasOne(SeoPmDsr::class, 'pm_id')
                ->where('type', 'daily')
                ->latestOfMany('report_date');
}
}
