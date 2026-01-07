<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubmissionSite extends Model
{
    protected $fillable = [
        'category_id',
        'website_name',
        'register_url',
        'category',
        'country',
        'moz_da',
        'spam_score',
        'traffic',
        'submission_type',
        'report_url'
    ];

    public function category()
    {
        return $this->belongsTo(\App\Models\SubmissionCategory::class, 'category_id');
    }
    public function submissionCategory()
    {
        return $this->belongsTo(SubmissionCategory::class, 'category_id');
    }
}
