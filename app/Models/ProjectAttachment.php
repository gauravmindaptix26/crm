<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'file_path', 'original_name', 'mime_type',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
