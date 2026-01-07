<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectCategory extends Model
{
    use HasFactory; // This is the correct way to use HasFactory

    protected $fillable = ['name', 'parent_id','created_by'];

    public function parent()
    {
        return $this->belongsTo(ProjectCategory::class, 'parent_id');
    }
    public function subcategories()
    {
        return $this->hasMany(ProjectCategory::class, 'parent_id');
    }
    public function children()
    {
        return $this->hasMany(ProjectCategory::class, 'parent_id');
    }
    public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}

}
