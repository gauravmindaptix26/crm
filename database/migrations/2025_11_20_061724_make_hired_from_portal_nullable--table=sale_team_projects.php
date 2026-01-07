<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sale_team_projects', function (Blueprint $table) {
            // Make both columns nullable
            $table->enum('hired_from_portal', ['PPH', 'Upwork', 'Fiver'])
                  ->nullable()
                  ->change();

            $table->bigInteger('hired_from_profile_id')
                  ->unsigned()
                  ->nullable()
                  ->change();
        });
    }

    public function down()
    {
        Schema::table('sale_team_projects', function (Blueprint $table) {
            $table->enum('hired_from_portal', ['PPH', 'Upwork', 'Fiver'])
                  ->nullable(false)
                  ->change();

            $table->bigInteger('hired_from_profile_id')
                  ->unsigned()
                  ->nullable(false)
                  ->change();
        });
    }
};