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
        Schema::table('dsrs', function (Blueprint $table) {
            // Modify the helped_by column to remove UNSIGNED
            $table->bigInteger('helped_by', false, true)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dsrs', function (Blueprint $table) {
            // Optionally, revert back to UNSIGNED if needed
            $table->bigInteger('helped_by')->nullable(false)->unsigned()->change();
        });
    }
};
