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
        Schema::table('projects', function (Blueprint $table) {
            $table->enum('project_status', ['Complete', 'Hold', 'Paused', 'Working', 'Issues', 'Temp Hold'])->nullable();
            $table->date('status_date')->nullable();
            $table->text('reason_description')->nullable();
            $table->enum('can_client_rehire', ['Yes', 'No'])->nullable();
            $table->date('rehire_date')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['project_status', 'status_date', 'reason_description', 'can_client_rehire', 'rehire_date']);
        });
    }
};
