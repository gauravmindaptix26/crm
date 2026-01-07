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
            $table->unsignedBigInteger('upsell_employee_id')->nullable()->after('team_lead_id');
            $table->unsignedBigInteger('content_manager_id')->nullable()->after('upsell_employee_id');
    
            $table->foreign('upsell_employee_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('content_manager_id')->references('id')->on('users')->onDelete('set null');
        });
    }
    
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['upsell_employee_id']);
            $table->dropForeign(['content_manager_id']);
            $table->dropColumn(['upsell_employee_id', 'content_manager_id']);
        });
    }
};
