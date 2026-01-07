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
            $table->string('expected_salary')->nullable()->change();
            $table->string('offered_salary')->nullable()->change();
            $table->string('current_salary')->nullable()->change();

            $table->string('experience')->nullable()->change();

        });
    }

    public function down()
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->string('expected_salary')->nullable(false)->change();
            $table->string('offered_salary')->nullable()->change();
            $table->string('current_salary')->nullable(false)->change();


            $table->string('experience')->nullable(false)->change();

        });
    }
};
