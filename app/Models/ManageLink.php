<?php

namespace App\Models;
use App\Models\ProjectTask;

use Illuminate\Database\Eloquent\Model;

class ManageLink extends Model
{
    protected $fillable = ['project_task_id', 'link', 'pa', 'da'];

    public function projectTask()
    {
        return $this->belongsTo(ProjectTask::class);
    }
}
