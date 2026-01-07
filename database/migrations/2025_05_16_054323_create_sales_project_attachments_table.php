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
        Schema::create('sales_project_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_project_id')
                  ->constrained('sale_team_projects') // ✅ Correct table name
                  ->onDelete('cascade');
            $table->string('file_path');
            $table->timestamps();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_project_attachments');
    }
};
