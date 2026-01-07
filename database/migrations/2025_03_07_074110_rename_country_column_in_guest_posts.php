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
        Schema::table('guest_posts', function (Blueprint $table) {
            $table->renameColumn('country', 'country_id');
        });
    }
    
    public function down()
    {
        Schema::table('guest_posts', function (Blueprint $table) {
            $table->renameColumn('country_id', 'country');
        });
    }
};
