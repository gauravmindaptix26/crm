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
            $table->unsignedBigInteger('project_category_id')->after('id');
            $table->unsignedBigInteger('project_subcategory_id')->nullable()->after('project_category_id');

            $table->foreign('project_category_id')->references('id')->on('project_categories')->onDelete('cascade');
            $table->foreign('project_subcategory_id')->references('id')->on('project_categories')->onDelete('cascade');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['project_category_id']);
            $table->dropForeign(['project_subcategory_id']);
            $table->dropColumn(['project_category_id', 'project_subcategory_id']);
        });
    }
};
