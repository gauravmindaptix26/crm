<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('leave_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., 'Paid Leave'
            $table->integer('days_per_quarter')->default(3);
            $table->integer('probation_months')->default(3);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('leave_policies');
    }
};