<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTask extends Model
{
    use HasFactory;
    protected $table = 'project_tasks';

    protected $fillable = [
        'country_id',
        'task_phase_id',
        'title',
        'description',
        'attachments',
        'video_link',
        'tool_link',
        'order_number',
        'created_by',
        'project_task_id',
        'file_path',
    ];

    protected $casts = [
        'attachments' => 'array', // Ensure attachments are stored as JSON
    ];

    // Relationships
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function taskPhase()
    {
        return $this->belongsTo(TaskPhase::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}
public function attachments() {
    return $this->hasMany(ProjectTaskAttachment::class);
}
public function phase()
{
    return $this->belongsTo(TaskPhase::class, 'task_phase_id');
}
}
