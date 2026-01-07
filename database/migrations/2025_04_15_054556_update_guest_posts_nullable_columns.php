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
        $table->string('industry')->nullable()->change(); // Makes industry nullable
        $table->string('traffic')->nullable()->change();  // Makes traffic nullable
        $table->string('publisher')->nullable()->change(); // Makes publisher nullable
        $table->decimal('publisher_price', 10, 2)->nullable()->change(); // Makes publisher_price nullable
        $table->decimal('our_price', 10, 2)->nullable()->change(); // Makes our_price nullable
        $table->text('publisher_details')->nullable()->change(); // Makes publisher_details nullable
        $table->string('live_link')->nullable()->change(); // Makes live_link nullable
        $table->unsignedBigInteger('country_id')->nullable()->change(); // Makes country_id nullable
    });
}

public function down()
{
    Schema::table('guest_posts', function (Blueprint $table) {
        $table->string('industry')->nullable(false)->change();
        $table->string('traffic')->nullable(false)->change();
        $table->string('publisher')->nullable(false)->change();
        $table->decimal('publisher_price', 10, 2)->nullable(false)->change();
        $table->decimal('our_price', 10, 2)->nullable(false)->change();
        $table->text('publisher_details')->nullable(false)->change();
        $table->string('live_link')->nullable(false)->change();
        $table->unsignedBigInteger('country_id')->nullable(false)->change();
    });
}
};
