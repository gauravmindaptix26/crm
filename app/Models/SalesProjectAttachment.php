<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesProjectAttachment extends Model
{
    protected $fillable = ['sales_project_id', 'file_path'];

    public function salesProject()
    {
        return $this->belongsTo(SalesProject::class);
    }
}
