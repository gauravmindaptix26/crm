<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_pm_dsrs', function (Blueprint $table) {
            // These 3 were missing and causing the error
            if (!Schema::hasColumn('seo_pm_dsrs', 'seo_discovery_post')) {
                $table->boolean('seo_discovery_post')->default(0);
            }
            if (!Schema::hasColumn('seo_pm_dsrs', 'weekly_team_session')) {
                $table->boolean('weekly_team_session')->default(0);
            }
            if (!Schema::hasColumn('seo_pm_dsrs', 'seo_video_shared')) {
                $table->boolean('seo_video_shared')->default(0);
            }

            // Also add the other missing Yes/No columns (so you don't get errors later)
            $missing = [
                'checked_teammate_dsr',
                'audited_project',
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
            ];

            foreach ($missing as $col) {
                if (!Schema::hasColumn('seo_pm_dsrs', $col)) {
                    $table->boolean($col)->default(0);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('seo_pm_dsrs', function (Blueprint $table) {
            $table->dropColumnIfExists('seo_discovery_post');
            $table->dropColumnIfExists('weekly_team_session');
            $table->dropColumnIfExists('seo_video_shared');
            $table->dropColumnIfExists('checked_teammate_dsr');
            $table->dropColumnIfExists('audited_project');
            $table->dropColumnIfExists('pr_placements');
            $table->dropColumnIfExists('guest_post_backlinking');
            $table->dropColumnIfExists('website_redesign');
            $table->dropColumnIfExists('blog_writing_seo');
            $table->dropColumnIfExists('virtual_assistant');
            $table->dropColumnIfExists('full_web_development');
            $table->dropColumnIfExists('crm_setup');
            $table->dropColumnIfExists('google_ads');
            $table->dropColumnIfExists('social_ads');
            $table->dropColumnIfExists('logo_redesign');
            $table->dropColumnIfExists('podcast_outreach');
            $table->dropColumnIfExists('video_testimonial');
            $table->dropColumnIfExists('google_reviews_service');
        });
    }
};