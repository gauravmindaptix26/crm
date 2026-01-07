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
        DB::statement("ALTER TABLE `sale_team_projects` 
            MODIFY COLUMN `business_type` ENUM('Midlevel', 'Startup', 'Small', 'Enterprise') 
            NULL DEFAULT NULL;");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `sale_team_projects` 
            MODIFY COLUMN `business_type` ENUM('Midlevel', 'Startup', 'Small', 'Enterprise') 
            NOT NULL;");
    }
};
