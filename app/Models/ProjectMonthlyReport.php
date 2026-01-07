<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMonthlyReport extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'report_for_month', 'details', 'added_by'];

    public function project() {
        return $this->belongsTo(Project::class);
    }

    public function addedBy() {
        return $this->belongsTo(User::class, 'added_by');
    }
}

