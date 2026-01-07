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
        Schema::table('sale_team_projects', function (Blueprint $table) {
            $table->enum('project_type', ['Ongoing', 'One-time'])
                  ->nullable()
                  ->default(null)
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('sale_team_projects', function (Blueprint $table) {
            $table->enum('project_type', ['Ongoing', 'One-time'])
                  ->default('Ongoing')
                  ->nullable(false)
                  ->change();
        });
    }
};
