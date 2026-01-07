<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignedProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'hour',
        'project_manager_id',
        'team_lead_id',
        'assigned_employee_id',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function projectManager()
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function teamLead()
    {
        return $this->belongsTo(User::class, 'team_lead_id');
    }

    public function assignedEmployee()
    {
        return $this->belongsTo(User::class, 'assigned_employee_id');
    }
    public function projectSale()
{
    return $this->belongsTo(SaleTeamProject::class, 'project_id');
}

}
