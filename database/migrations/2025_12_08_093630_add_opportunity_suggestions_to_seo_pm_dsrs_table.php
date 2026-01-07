<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('seo_pm_dsrs', function (Blueprint $table) {
            $table->boolean('pr_placements')->default(false)->after('additional_tasks');
            $table->boolean('guest_post_backlinking')->default(false);
            $table->boolean('website_redesign')->default(false);
            $table->boolean('blog_writing_seo')->default(false);
            $table->boolean('virtual_assistant')->default(false);
            $table->boolean('full_web_development')->default(false);
            $table->boolean('crm_setup')->default(false);
            $table->boolean('google_ads')->default(false);
            $table->boolean('social_ads')->default(false);
            $table->boolean('logo_redesign')->default(false);
            $table->boolean('podcast_outreach')->default(false);
            $table->boolean('video_testimonial')->default(false);
            $table->boolean('google_reviews_service')->default(false);
            $table->text('opportunity_description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seo_pm_dsrs', function (Blueprint $table) {
            $table->dropColumn([
                'pr_placements', ' guest_post_backlinking', 'website_redesign',
                'blog_writing_seo', 'virtual_assistant', 'full_web_development',
                'crm_setup', 'google_ads', 'social_ads', 'logo_redesign',
                'podcast_outreach', 'video_testimonial', 'google_reviews_service',
                'opportunity_description'
            ]);
        });
    }
};
