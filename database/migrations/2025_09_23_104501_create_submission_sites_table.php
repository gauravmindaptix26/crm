<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('submission_sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('submission_categories')->onDelete('cascade');
            $table->string('website_name');
            $table->string('register_url')->nullable();
            $table->string('category')->nullable(); // e.g., General, Business
            $table->string('country')->nullable();
            $table->integer('moz_da')->nullable();      // manually entered
            $table->integer('spam_score')->nullable();  // manually entered
            $table->string('traffic')->nullable();      // e.g., '1.1B'
            $table->string('submission_type')->nullable(); // Free / Paid
            $table->string('report_url')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_sites');
    }
};
