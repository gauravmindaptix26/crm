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
        Schema::create('web_dev_pm_dsrs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pm_id')->constrained('users')->onDelete('cascade');
            $table->date('report_date');
            $table->integer('upwork_bids')->nullable();  // Digit
            $table->integer('pph_bids')->nullable();     // Digit
            $table->text('fiverr_maintain')->nullable(); // Text
            $table->text('dribbble_jobs')->nullable();   // Text/digit
            $table->text('online_jobs_apply')->nullable(); // Textarea
            $table->json('marketplace_files')->nullable(); // Multiple files JSON
            $table->text('old_clients_design')->nullable(); // Textarea
            $table->text('old_leads_ask_work')->nullable(); // Text/digit
            $table->text('client_communication')->nullable(); // Textarea
            $table->text('current_client_more_work')->nullable(); // Textarea
            $table->text('project_completion_on_time')->nullable(); // Text/digit
            $table->text('meet_pm_more_work')->nullable(); // Text/digit
            $table->json('task_hours')->nullable(); // Hours JSON per section
            $table->json('proofs')->nullable();     // Proofs JSON for tasks
            $table->tinyInteger('rating')->unsigned()->default(0); // Self-rating
            $table->tinyInteger('coo_rating')->unsigned()->nullable(); // COO rating
            $table->text('coo_notes')->nullable();
            $table->foreignId('coo_reviewed_by')->nullable()->constrained('users');
            $table->timestamp('coo_reviewed_at')->nullable();
            $table->string('type')->default('daily');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_dev_pm_dsrs');
    }
};
