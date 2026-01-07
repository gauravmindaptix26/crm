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
        Schema::table('seo_pm_dsrs', function (Blueprint $table) {
            // Remove the default value and make it nullable
            $table->enum('coo_status', ['excellent', 'good', 'average'])
                  ->nullable()
                  ->default(null)
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seo_pm_dsrs', function (Blueprint $table) {
            // Restore default to 'average' if needed
            $table->enum('coo_status', ['excellent', 'good', 'average'])
                  ->default('average')
                  ->change();
        });
    }
};