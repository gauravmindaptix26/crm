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
            $table->string('source_type')->default('sale_team')->after('project_id');
        });
    }

    public function down(): void
    {
        Schema::table('assigned_projects', function (Blueprint $table) {
            $table->dropColumn('source_type');
        });
    }
};
