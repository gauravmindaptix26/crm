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
        Schema::create('employee_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');           // FK -> users
            $table->unsignedBigInteger('project_manager_id');   // who submitted
            $table->unsignedBigInteger('department_id')->nullable();
            $table->date('review_month');                       // store first day of month, e.g. 2025-08-01
            $table->tinyInteger('quality_of_work')->unsigned(); // 1-10
            $table->tinyInteger('communication')->unsigned();   // 1-10
            $table->tinyInteger('ownership')->unsigned();       // 1-10
            $table->tinyInteger('team_collaboration')->unsigned(); // 1-10
            $table->decimal('overall_rating', 4, 2)->nullable(); // average 1.00-10.00
            $table->text('comments')->nullable();
            $table->timestamps();
    
            $table->unique(['employee_id','review_month']); // one review per employee per month
            $table->index('project_manager_id');
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_manager_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::dropIfExists('employee_reviews');
}
};
