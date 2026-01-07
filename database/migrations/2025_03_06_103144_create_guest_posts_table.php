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
        Schema::create('guest_posts', function (Blueprint $table) {
            $table->id();
            $table->string('website');
            $table->integer('da'); // Domain Authority
            $table->integer('pa'); // Page Authority
            $table->string('industry')->nullable(); // Industry
            $table->string('country')->nullable(); // Country
            $table->integer('traffic'); // Traffic
            $table->string('publisher'); // Publisher
            $table->decimal('publisher_price', 10, 2); // Publisher Price
            $table->text('publisher_details')->nullable(); // Publisher Details
            $table->string('live_link')->nullable(); // Live link from publisher
            $table->decimal('our_price', 10, 2); // Our Price
            $table->timestamps(); // Created At & Updated At
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_posts');
    }
};
