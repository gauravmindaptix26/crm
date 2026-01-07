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
            // Daily Tasks Recommended by Company
            $table->tinyInteger('checked_teammate_dsr')->default(0)->after('rating');
            $table->tinyInteger('audited_project')->default(0)->after('checked_teammate_dsr');
            $table->text('daily_tasks_notes')->nullable()->after('audited_project');

            // Enhanced SEO Discovery (Weekly)
            $table->tinyInteger('wrote_seo_post')->default(0)->after('daily_tasks_notes');
            $table->tinyInteger('conducted_seo_session')->default(0)->after('wrote_seo_post');
            $table->tinyInteger('watched_seo_video')->default(0)->after('conducted_seo_session');
            $table->text('seo_discovery_notes')->nullable()->after('watched_seo_video');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seo_pm_dsrs', function (Blueprint $table) {
            $table->dropColumn([
                'checked_teammate_dsr', 'audited_project', 'daily_tasks_notes',
                'wrote_seo_post', 'conducted_seo_session', 'watched_seo_video', 'seo_discovery_notes'
            ]);
        });
    }
};
