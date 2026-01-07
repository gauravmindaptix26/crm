<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Task;

class SubmittedTask extends Model
{
    use HasFactory;

    protected $guarded = []; // or define $fillable if you want more control

    // Optional: define table if not following Laravel convention
    // protected $table = 'your_table_name';

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class); // assumes user_id
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by'); // assumes assigned_by column
    }
}
