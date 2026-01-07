<?php

namespace App\Models;
use App\Models\User;
use App\Models\Department;
use App\Models\Dsr;
use App\Models\ProjectAttachment;
use App\Models\ProjectPayment;





use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name_or_url', 'dashboard_url', 'description', 'project_grade', 'business_type',
        'project_category_id', 'project_subcategory_id', 'country_id', 'task_phases',
        'project_manager_id', 'assign_main_employee_id', 'price', 'estimated_hours',
        'project_type', 'upwork_project_type', 'client_type', 'report_type', 'project_month',
        'sales_person_id', 'created_by', 'department_id', 'client_name', 'client_email', 'client_other_info',
        'additional_employees','project_status', 'status_date','reason_description', 'can_client_rehire', 'closed_by','rehire_date','team_lead_id','upsell_employee_id','content_manager_id','content_details','sale_team_project_id','last_followup_at'
    ];

    protected $casts = [
        'task_phases' => 'array',
        'additional_employees' => 'array',
        'project_month' => 'date',
        'status_date' => 'date',
        'rehire_date' => 'date',
        'content_type' => 'array',
        'content_quantity' => 'array',
        'last_followup_at' => 'datetime',
    ];

    public function projectManager()
{
    return $this->belongsTo(User::class, 'project_manager_id'); // Assuming the column storing PM ID is 'project_manager_id'
}
public function salesPerson()
{
    return $this->belongsTo(User::class, 'sales_person_id');
}
public function attachments()
{
    return $this->hasMany(ProjectAttachment::class);
}
public function projectPayments()
{
    return $this->hasMany(ProjectPayment::class, 'project_id');
}


public function department()
{
    return $this->belongsTo(Department::class, 'department_id');
}
public function country()
{
    return $this->belongsTo(Country::class, 'country_id');
}
public function projectCategory()
{
    return $this->belongsTo(ProjectCategory::class, 'project_category_id');
}
public function projectSubCategory()
{
    return $this->belongsTo(ProjectCategory::class, 'project_subcategory_id');
}
    public function closedByUser()
{
    return $this->belongsTo(User::class, 'closed_by');
}

    public function payments()
 {
    return $this->hasMany(ProjectPayment::class);
}
public function assignMainEmployee()
{
    return $this->belongsTo(User::class, 'assign_main_employee_id');
}

public function dsrs()
{
    return $this->hasMany(DSR::class); // Adjust if named differently
}

public function getAssignedEmployeesAttribute()
{
    $ids = json_decode($this->additional_employees ?? '[]', true);
    return User::whereIn('id', $ids)->get();
}
// Project.php
public function teamLead()
{
    return $this->belongsTo(User::class, 'team_lead_id');
}
public function upsellEmployee()
{
    return $this->belongsTo(User::class, 'upsell_employee_id');
}

public function contentManager()
{
    return $this->belongsTo(User::class, 'content_manager_id');
}
public function saleTeamOriginal()
{
    return $this->hasOne(SaleTeamProject::class, 'id', 'sale_team_project_id');
}
public function employee()
{
    return $this->belongsTo(User::class, 'assign_main_employee_id');
}

public function getDisplayPriceAttribute()
{
    // For sales team projects, fallback to price_usd from SaleTeamProject if local price is missing
    if ($this->source_type === 'sale_team') {
        return $this->price ?? SaleTeamProject::where('id', $this->sale_team_project_id)->value('price_usd') ?? 0;
    }

    // For internal projects, just return local price (or 0 fallback)
    return $this->price ?? 0;
}
public function getDisplayHoursAttribute()
{
    // If it's a sale_team project AND estimated_hours is null, fallback to assigned hour
    if ($this->source_type === 'sale_team') {
        if (!is_null($this->estimated_hours)) {
            return $this->estimated_hours;
        }

        // fallback to assigned_projects.hour
        $assigned = \App\Models\AssignedProject::where('project_id', $this->id)->first();
        return $assigned?->hour ?? null;
    }

    // For internal projects, just use estimated_hours
    return $this->estimated_hours;
}
public function getDisplayDescriptionAttribute()
{
    // If assigned from sales and description is empty/null, fallback to SaleTeamProject
    if ($this->source_type === 'sale_team' && empty($this->description)) {
        $saleProject = \App\Models\SaleTeamProject::find($this->sale_team_project_id);
        return $saleProject?->description ?? '';
    }

    // Otherwise use the PM's description
    return $this->description ?? '';
}
// Project.php
public function saleTeamAttachments()
{
    return $this->hasMany(
        \App\Models\SalesProjectAttachment::class,
        'sales_project_id',
        'sale_team_project_id'
    );
}

public function followups()
    {
        return $this->hasMany(ProjectFollowup::class);
    }
    public function hiredFrom()
{
    return $this->belongsTo(HiredFrom::class, 'hired_from_id');
}
}
