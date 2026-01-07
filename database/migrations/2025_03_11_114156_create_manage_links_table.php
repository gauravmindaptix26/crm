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
        Schema::create('manage_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_task_id')->constrained()->onDelete('cascade');
            $table->string('link');
            $table->integer('pa')->unsigned();
            $table->integer('da')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manage_links');
    }
};
