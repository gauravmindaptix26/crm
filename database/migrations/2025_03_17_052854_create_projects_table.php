<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name_or_url');
            $table->string('dashboard_url')->nullable();
            $table->text('description')->nullable();
            $table->enum('project_grade', ['A', 'AA', 'AAA'])->nullable();
            $table->enum('business_type', ['Startup', 'Small', 'Mid-level', 'Enterprise'])->nullable();
            $table->unsignedBigInteger('project_main_category_id');
            $table->unsignedBigInteger('project_sub_category_id')->nullable();
            $table->unsignedBigInteger('country_id');
            $table->json('task_phases')->nullable();
            $table->unsignedBigInteger('project_manager_id');
            $table->unsignedBigInteger('assign_main_employee_id');
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('estimated_hours')->nullable();
            $table->enum('project_type', ['Ongoing', 'One-time'])->nullable();
            $table->enum('upwork_project_type', ['Hourly', 'Fixed'])->nullable();
            $table->enum('client_type', ['New Client', 'Old Client'])->nullable();
            $table->enum('report_type', ['Weekly', 'Bi-Weekly', 'Monthly'])->nullable();
            $table->date('project_month')->nullable();
            $table->unsignedBigInteger('sales_person_id');
            $table->unsignedBigInteger('department_id');
            $table->string('client_name')->nullable();
            $table->string('client_email')->nullable();
            $table->text('client_other_info')->nullable();
            $table->json('additional_employees')->nullable();
            $table->unsignedBigInteger('created_by');  // Added this column
            $table->timestamps();
    
            // Foreign key for created_by
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
