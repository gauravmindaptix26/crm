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
        Schema::table('candidates', function (Blueprint $table) {
            $table->string('current_salary', 255)->change();
            $table->string('expected_salary', 255)->change();
            $table->string('offered_salary', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->decimal('current_salary', 10, 2)->change();
            $table->decimal('expected_salary', 10, 2)->change();
            $table->decimal('offered_salary', 10, 2)->change();
        });
    }
};
