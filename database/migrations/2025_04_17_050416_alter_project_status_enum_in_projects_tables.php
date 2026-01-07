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
        Schema::table('projects_tables', function (Blueprint $table) {
            //
        });
        DB::statement("ALTER TABLE projects 
        MODIFY project_status 
        ENUM('Complete', 'Hold', 'Paused', 'Working', 'Issues', 'Temp Hold', 'Closed') 
        NOT NULL");


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects_tables', function (Blueprint $table) {
            //
        });
        DB::statement("ALTER TABLE projects 
        MODIFY project_status 
        ENUM('Complete', 'Hold', 'Paused', 'Working', 'Issues', 'Temp Hold') 
        NOT NULL");




    }
};
