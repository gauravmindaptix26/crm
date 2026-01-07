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
        // Step 1: Update existing data to valid new enum values
        DB::table('sale_team_projects')
            ->where('project_type', 'One time')
            ->update(['project_type' => 'One-time']);

        // Step 2: Change the enum definition
        Schema::table('sale_team_projects', function (Blueprint $table) {
            $table->enum('project_type', ['Ongoing', 'One-time'])
                ->default('Ongoing')
                ->change();
        });
    }

    public function down(): void
    {
        // Revert enum and values if rolled back
        DB::table('sale_team_projects')
            ->where('project_type', 'One-time')
            ->update(['project_type' => 'One time']);

        Schema::table('sale_team_projects', function (Blueprint $table) {
            $table->enum('project_type', ['Ongoing', 'One time'])
                ->default('Ongoing')
                ->change();
        });
    }
};
