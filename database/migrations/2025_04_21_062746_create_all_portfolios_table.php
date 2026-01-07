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
        Schema::create('all_portfolios', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('country_id');
            $table->unsignedBigInteger('department_id');
            $table->text('description')->nullable();
            $table->string('attachment')->nullable(); // store file name/path
            $table->unsignedBigInteger('created_by')->nullable(); // track added by
            $table->timestamps();
    
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('all_portfolios');
    }
};
