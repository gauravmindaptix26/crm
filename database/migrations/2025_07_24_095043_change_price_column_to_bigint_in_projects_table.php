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
        // Disable strict mode temporarily
        DB::statement('SET sql_mode = ""');
    
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedBigInteger('price')->nullable()->change();
        });
    
        // Restore default strict mode
        DB::statement('SET sql_mode = "STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION"');
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->change();
        });
    }
};
