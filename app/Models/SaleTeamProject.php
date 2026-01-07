<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SalesProjectAttachment;
use App\Models\AssignedProject;



class SaleTeamProject extends Model
{
    protected $table= 'sale_team_projects';
    
    protected $fillable = [
        'hired_from_portal', 'hired_from_profile_id', 'name_or_url', 'description',
        'price_usd', 'project_type', 'client_type', 'business_type', 'project_month',
        'country_id', 'sales_person_id', 'department_id', 'client_name', 'client_email',
        'time_to_contact', 'client_other_info', 'client_behaviour', 'communication_details',
        'specific_keywords', 'result_commitment', 'website_speed_included','website_dev_commitment', 'internal_explainer_video', 'content_commitment'
    ];
    protected $casts = [
        'additional_employees' => 'array', // Automatically decode JSON to array
    ];
    public function country() { 
        return $this->belongsTo(Country::class); 
    }
    public function salesPerson() { 
        return $this->belongsTo(User::class, 'sales_person_id');
     }
    public function department() { 
        return $this->belongsTo(Department::class);
     }
    public function hiredFromProfile() { 
        return $this->belongsTo(HiredFrom::class, 'hired_from_profile_id'); 
    }
    // In SaleTeamProject.php
public function attachments()
{
    return $this->hasMany(SalesProjectAttachment::class, 'sales_project_id');
}
public function assignments()
{
    return $this->hasMany(AssignedProject::class, 'sale_team_project_id');
}
public function projectManager()
{
    return $this->belongsTo(User::class, 'project_manager_id');
}
public function projectCategory()
{
    return $this->belongsTo(ProjectCategory::class, 'project_category_id');
}
public function projectSubCategory()
{
    return $this->belongsTo(ProjectCategory::class, 'project_subcategory_id');
}
public function upsellEmployee()
{
    return $this->belongsTo(User::class, 'upsell_employee_id');
}
public function contentManager()
{
    return $this->belongsTo(User::class, 'content_manager_id');
}
public function projectPayments()
{
    return $this->hasMany(ProjectPayment::class, 'project_id');
}

public function teamLead()
{
    return $this->belongsTo(User::class, 'team_lead_id');
}

public function assignMainEmployee()
{
    return $this->belongsTo(User::class, 'assigned_employee_id');
}
}
