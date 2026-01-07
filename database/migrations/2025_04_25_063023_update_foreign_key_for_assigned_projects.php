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
        Schema::table('assigned_projects', function (Blueprint $table) {
            // Drop the old foreign key
            $table->dropForeign(['project_id']);

            // Add the new foreign key referencing the sale_team_projects table
            $table->foreign('project_id')
                  ->references('id')->on('sale_team_projects')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assigned_projects', function (Blueprint $table) {
            // Drop the foreign key if rolled back
            $table->dropForeign(['project_id']);
            
            // Revert to the old foreign key referencing the projects table
            $table->foreign('project_id')
                  ->references('id')->on('projects')
                  ->onDelete('cascade');
        });
    }
};
