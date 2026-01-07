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
        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('country_id');
            $table->unsignedBigInteger('task_phase_id'); // Updated from project_phase_id to task_phase_id
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('attachments')->nullable();
            $table->string('video_link')->nullable();
            $table->string('tool_link')->nullable();
            $table->integer('order_number')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
    
            // Foreign Key Constraints
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->foreign('task_phase_id')->references('id')->on('task_phases')->onDelete('cascade'); // Corrected table name
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_tasks');
    }
};
