<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('link_buildings', function (Blueprint $table) {
            $table->id();
            $table->string('website');
            $table->integer('pa');
            $table->integer('da');
            $table->json('niche'); // Store as JSON
            $table->json('countries'); // Store as JSON
            $table->string('type_of_link');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('link_buildings');
    }
};
