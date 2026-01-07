<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebDevPmDsr extends Model
{
    use HasFactory;

    protected $fillable = [
        'pm_id', 'report_date', 'type', 'rating', 'coo_rating', 'coo_notes', 'coo_reviewed_by', 'coo_reviewed_at',
        'upwork_bids', 'pph_bids', 'fiverr_maintain', 'dribbble_jobs', 'online_jobs_apply', 'marketplace_files',
        'old_clients_design', 'old_leads_ask_work', 'client_communication', 'current_client_more_work',
        'project_completion_on_time', 'meet_pm_more_work', 'task_hours', 'proofs'
    ];

    protected $casts = [
        'report_date' => 'date',
        'marketplace_files' => 'array', // JSON for files
        'task_hours' => 'array', // JSON for hours
        'proofs' => 'array', // JSON for proofs
    ];

    public function pm()
    {
        return $this->belongsTo(User::class, 'pm_id');
    }

    public function cooReviewer()
    {
        return $this->belongsTo(User::class, 'coo_reviewed_by');
    }
}