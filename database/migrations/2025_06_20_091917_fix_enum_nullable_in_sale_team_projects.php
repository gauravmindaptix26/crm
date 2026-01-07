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
        // Step 1: Convert existing 'One time' values to 'One-time' to match ENUM
        DB::table('sale_team_projects')
            ->where('project_type', 'One time')
            ->update(['project_type' => 'One-time']);

        // Step 2: Use raw SQL to update the ENUM to match the correct format and make nullable
        DB::statement("ALTER TABLE `sale_team_projects` 
            MODIFY COLUMN `project_type` ENUM('Ongoing', 'One-time') 
            NULL DEFAULT NULL;");
    }

    public function down(): void
    {
        // Revert ENUM back if needed (optional)
        DB::statement("ALTER TABLE `sale_team_projects` 
            MODIFY COLUMN `project_type` ENUM('Ongoing', 'One time') 
            NOT NULL;");
    }
};
