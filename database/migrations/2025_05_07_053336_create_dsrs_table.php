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
        Schema::create('dsrs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->text('work_description');
            $table->integer('hours');
            $table->foreignId('helped_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('help_description')->nullable();
            $table->integer('help_rating')->nullable(); // 1 to 5
            $table->boolean('replied_to_emails')->default(false);
            $table->boolean('sent_report')->default(false);
            $table->boolean('justified_work')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dsrs');
    }
};
