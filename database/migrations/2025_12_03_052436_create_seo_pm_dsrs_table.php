<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('seo_pm_dsrs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pm_id'); // project manager id
            $table->date('report_date');
    
            // Remarketing / Upsell
            $table->boolean('follow_paused_clients')->default(0);
            $table->boolean('follow_closed_clients')->default(0);
            $table->text('follow_closed_detail')->nullable();
            $table->boolean('upsell_clients')->default(0);
            $table->boolean('referral_client')->default(0);
    
            // Case Studies / Portfolio / Reviews
            $table->boolean('updated_case_study')->default(0);
            $table->boolean('collected_review')->default(0);
    
            // SEO Discovery (Weekly)
            $table->boolean('wrote_post')->default(0);
            $table->text('wrote_post_detail')->nullable();
            $table->boolean('weekly_team_session')->default(0);
            $table->boolean('video_shared')->default(0);
    
            // Invoices
            $table->integer('invoices_sent')->nullable();
            $table->integer('invoices_pending')->nullable();
            $table->integer('payment_followups')->nullable();
    
            // Happy Things
            $table->integer('paused_today')->nullable();
            $table->integer('restarted_today')->nullable();
            $table->integer('closed_today')->nullable();
    
            // Daily Production Work
            $table->integer('meetings_completed')->nullable();
            $table->text('client_queries_resolved')->nullable();
            $table->text('additional_tasks')->nullable();
    
            // Rating (out of 10)
            $table->tinyInteger('rating')->nullable();
    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_pm_dsrs');
    }
};
