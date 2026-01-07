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
            // Description for "Daily Tasks Recommended by the Company"
            $table->text('daily_tasks_description')->nullable()->after('audited_project');

            // Description for "SEO Discovery (Weekly Task)"
            $table->text('seo_discovery_description')->nullable()->after('daily_tasks_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seo_pm_dsrs', function (Blueprint $table) {
            $table->dropColumn(['daily_tasks_description', 'seo_discovery_description']);
        });
    }
};
