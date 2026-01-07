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
        Schema::table('project_categories', function (Blueprint $table) {
            // Step 1: Drop the existing foreign key
            $table->dropForeign(['parent_id']);

            // Step 2: Re-add the foreign key with SET NULL on delete
            $table->foreign('parent_id')
                ->references('id')
                ->on('project_categories')
                ->onDelete('SET NULL'); // Allow setting parent_id to NULL
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('project_categories', function (Blueprint $table) {
            // Rollback: Drop the modified foreign key
            $table->dropForeign(['parent_id']);

            // Restore the original foreign key with CASCADE on delete
            $table->foreign('parent_id')
                ->references('id')
                ->on('project_categories')
                ->onDelete('cascade');
        });
    }
};
