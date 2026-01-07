<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectTaskAttachment extends Model
{
    protected $fillable = ['project_task_id', 'file_path'];

    public function projectTask() {
        return $this->belongsTo(ProjectTask::class);
    }
}
