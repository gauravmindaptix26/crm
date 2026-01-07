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
            $table->json('payment_screenshots')->nullable()->after('payment_notes');
            // This will store array of file paths like: ["uploads/dsr/abc123.jpg", "uploads/dsr/xyz456.png"]
        });
    }

    public function down()
    {
        Schema::table('seo_pm_dsrs', function (Blueprint $table) {
            $table->dropColumn('payment_screenshots');
        });
    }
};
