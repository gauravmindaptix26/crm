<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'phone_number', 'experience', 'current_salary',
        'expected_salary', 'offered_salary', 'date_of_joining', 'comments',
        'resume','added_by', 'department_id', 'status'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function addedBy()
{
    return $this->belongsTo(User::class, 'added_by');
}
}
