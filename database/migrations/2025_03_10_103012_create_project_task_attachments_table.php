<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('project_task_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_task_id')->constrained()->onDelete('cascade'); // Links to project_tasks table
            $table->string('file_path'); // Stores the file path
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('project_task_attachments');
    }
};
