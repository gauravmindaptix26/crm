<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoPmDsr extends Model
{
    protected $table = 'seo_pm_dsrs';

    protected $fillable = [
        'pm_id',
        'report_date',
        'follow_paused_clients',
        'follow_closed_clients',
        'follow_closed_detail',
        'upsell_clients',
        'referral_client',
        'updated_case_study',
        'collected_review',
        'case_study_description',
        'invoices_sent',
        'invoices_pending',
        'payment_followups',
        'payment_notes',
        'paused_today',
        'restarted_today',
        'closed_today',
        'happy_things_notes',
        'meetings_completed',
        'client_queries_resolved',
        'additional_tasks',
        'checked_teammate_dsr',
        'audited_project',
        'daily_tasks_description',
        'seo_discovery_post',
        'weekly_team_session',
        'seo_video_shared',
        'seo_discovery_description',
        'pr_placements',
        'guest_post_backlinking',
        'website_redesign',
        'blog_writing_seo',
        'virtual_assistant',
        'full_web_development',
        'crm_setup',
        'google_ads',
        'social_ads',
        'logo_redesign',
        'podcast_outreach',
        'video_testimonial',
        'google_reviews_service',
        'opportunity_description',
        'rating',
        'proofs', // ← NEW
        'payment_screenshots',
        'task_hours',
        'type',
        'rating',
        'coo_status', 'coo_notes', 'coo_reviewed_by', 'coo_reviewed_at','coo_rating','production_projects',
    ];

    protected $casts = [
        'proofs' => 'array', // Automatically decode/encode JSON ↔ Array
        'follow_paused_clients' => 'boolean',
        'follow_closed_clients' => 'boolean',
        'upsell_clients' => 'boolean',
        'referral_client' => 'boolean',
        'updated_case_study' => 'boolean',
        'collected_review' => 'boolean',
        'checked_teammate_dsr' => 'boolean',
        'audited_project' => 'boolean',
        'seo_discovery_post' => 'boolean',
        'weekly_team_session' => 'boolean',
        'seo_video_shared' => 'boolean',
        'pr_placements' => 'boolean',
        'guest_post_backlinking' => 'boolean',
        'website_redesign' => 'boolean',
        'blog_writing_seo' => 'boolean',
        'virtual_assistant' => 'boolean',
        'full_web_development' => 'boolean',
        'crm_setup' => 'boolean',
        'google_ads' => 'boolean',
        'social_ads' => 'boolean',
        'logo_redesign' => 'boolean',
        'podcast_outreach' => 'boolean',
        'video_testimonial' => 'boolean',
        'google_reviews_service' => 'boolean',
        'rating' => 'integer',
        'payment_screenshots' => 'array',
        'task_hours' => 'array',
        'report_date' => 'date',
        'production_projects'   => 'array',
        // ... other integer fields
    ];

    public function pm()
    {
        return $this->belongsTo(User::class, 'pm_id');
    }

    // Optional: Helper to get proof for a field
    public function getProof($field)
    {
        return data_get($this->proofs, $field, null);
    }
}