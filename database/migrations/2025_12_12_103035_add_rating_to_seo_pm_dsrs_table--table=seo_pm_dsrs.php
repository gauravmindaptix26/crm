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
        $table->unsignedTinyInteger('rating')->default(0)->after('type'); // 0 to 10
    });
}

public function down()
{
    Schema::table('seo_pm_dsrs', function (Blueprint $table) {
        $table->dropColumn('rating');
    });
}
};
