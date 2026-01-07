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
        Schema::table('seo_pm_dsrs', function (Blueprint $table) {
            $table->enum('coo_status', ['excellent', 'good', 'average'])
                  ->default('average');
            $table->text('coo_notes')->nullable();
            $table->unsignedBigInteger('coo_reviewed_by')->nullable();
            $table->timestamp('coo_reviewed_at')->nullable();

            // Optional foreign key
            $table->foreign('coo_reviewed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('seo_pm_dsrs', function (Blueprint $table) {
            $table->dropForeign(['coo_reviewed_by']);
            $table->dropColumn(['coo_status', 'coo_notes', 'coo_reviewed_by', 'coo_reviewed_at']);
        });
    }
};
