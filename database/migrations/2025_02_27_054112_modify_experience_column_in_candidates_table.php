<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->string('experience')->change(); // Change INT to VARCHAR
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->integer('experience')->change(); // Rollback: VARCHAR to INT
        });
    }
};
