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
        Schema::create('sales_leads', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->string('client_email');
            $table->string('client_phone')->nullable();
            $table->string('job_title');
            $table->text('description')->nullable();
            $table->string('job_url')->nullable();
            $table->enum('client_type', ['Reseller', 'Premium', 'General']);
            $table->unsignedBigInteger('lead_from_id');
            $table->unsignedBigInteger('country_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('sales_person_id');
            $table->timestamps();
        
            $table->foreign('lead_from_id')->references('id')->on('hired_froms')->onDelete('cascade');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('sales_person_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_leads');
    }
};
