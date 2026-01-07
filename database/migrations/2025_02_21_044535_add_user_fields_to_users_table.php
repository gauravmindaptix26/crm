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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number')->nullable();
            $table->integer('monthly_target')->nullable();
            $table->decimal('upsell_incentive', 5, 2)->nullable();
            $table->string('user_role')->nullable();
            $table->unsignedBigInteger('reporting_person')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->boolean('allow_all_projects')->default(false);
            $table->boolean('disable_login')->default(false);
            $table->string('image')->nullable();
            $table->string('experience')->nullable();
            $table->string('qualification')->nullable();
            $table->string('specialization')->nullable();
            $table->date('date_of_joining')->nullable();
            $table->string('employee_code')->nullable();

            // Foreign keys
            $table->foreign('reporting_person')->references('id')->on('users')->onDelete('set null');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $table->dropColumn([
            'phone_number', 'monthly_target', 'upsell_incentive', 'user_role', 'reporting_person', 
            'department_id', 'allow_all_projects', 'disable_login', 'image', 'experience', 
            'qualification', 'specialization', 'date_of_joining', 'employee_code'
        ]);
    }
};
