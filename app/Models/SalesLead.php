<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesLead extends Model
{
    protected $fillable = [
        'client_name', 'client_email', 'client_phone', 'job_title',
        'description', 'job_url', 'client_type', 'lead_from_id',
        'country_id', 'department_id', 'sales_person_id'
    ];

    public function leadFrom() {
        return $this->belongsTo(HiredFrom::class, 'lead_from_id');
    }
    public function followUps()
    {
        return $this->hasMany(SalesLeadFollowUp::class, 'lead_id');
    }
    public function country() {
        return $this->belongsTo(Country::class);
    }

    public function department() {
        return $this->belongsTo(Department::class);
    }

    public function salesPerson() {
        return $this->belongsTo(User::class, 'sales_person_id');
    }
    public function notes()
{
    return $this->hasMany(SalesLeadNote::class);
}
public function user()
{
    return $this->belongsTo(User::class, 'sales_person_id');
}

}
