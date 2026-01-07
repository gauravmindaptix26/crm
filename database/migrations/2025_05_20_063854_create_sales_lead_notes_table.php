<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales_lead_notes', function (Blueprint $table) {
            $table->id();
    $table->unsignedBigInteger('sales_lead_id');
    $table->string('note_type'); // Follow up / General
    $table->string('title');
    $table->text('description')->nullable();
    $table->string('attachment')->nullable();
    $table->unsignedBigInteger('added_by')->nullable();
    $table->timestamps();

    $table->foreign('sales_lead_id')->references('id')->on('sales_leads')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_lead_notes');
    }
};
