<?php

// app/Models/ProjectPayment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectPayment extends Model
{
    protected $fillable = [
        'project_id', 'account_id', 'payment_amount', 'commission_amount', 'payment_month', 'payment_details', 'screenshot', 'created_by',
    ];
    public function account()
    {
        return $this->belongsTo(PaymentAccount::class, 'account_id');
    }
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

 
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
