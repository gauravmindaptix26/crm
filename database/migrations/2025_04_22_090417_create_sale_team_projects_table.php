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
        Schema::create('sale_team_projects', function (Blueprint $table) {
            $table->id();
            $table->enum('hired_from_portal', ['PPH', 'Upwork', 'Fiver']);
            $table->unsignedBigInteger('hired_from_profile_id');
            $table->string('name_or_url');
            $table->text('description')->nullable();
            $table->decimal('price_usd', 10, 2);
            $table->enum('project_type', ['Ongoing', 'One time']);
            $table->enum('client_type', ['new client', 'old client']);
            $table->enum('business_type', ['Midlevel', 'Startup', 'Small', 'Enterprise']);
            $table->date('project_month');
            $table->unsignedBigInteger('country_id');
            $table->unsignedBigInteger('sales_person_id');
            $table->unsignedBigInteger('department_id');
            $table->string('client_name');
            $table->string('client_email');
            $table->text('time_to_contact')->nullable();
            $table->text('client_other_info')->nullable();
            $table->text('client_behaviour')->nullable();
            $table->text('communication_details')->nullable();
            $table->text('specific_keywords')->nullable();
            $table->text('result_commitment')->nullable();
            $table->enum('website_speed_included', ['Yes', 'No']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_team_projects');
    }
};
