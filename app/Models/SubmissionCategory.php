<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubmissionCategory extends Model
{
    protected $fillable = ['name','slug','description'];

    public function sites()
    {
        return $this->hasMany(SubmissionSite::class, 'category_id');
    }
}
