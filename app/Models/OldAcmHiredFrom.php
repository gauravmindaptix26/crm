<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldAcmHiredFrom extends Model
{
    protected $connection = 'old_crm_db';
    protected $table = 'acm_hiredfrom';

    public $timestamps = false; // Disable if old table doesn't have Laravel-style timestamps

    protected $fillable = [
        'name',
        'desc',
        'isDeleted',
        'created_by_user_id',
        'created_at',
        'updated_at',
        'deleted_at',
        'deleted_by_user_id',
    ];
}
