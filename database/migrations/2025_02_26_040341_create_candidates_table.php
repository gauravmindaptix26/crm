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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone_number');
            $table->integer('experience');
            $table->decimal('current_salary', 10, 2);
            $table->decimal('expected_salary', 10, 2);
            $table->decimal('offered_salary', 10, 2)->nullable();
            $table->date('date_of_joining')->nullable();
            $table->text('comments')->nullable();
            $table->string('resume')->nullable();
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->enum('status', ['Shortlist', 'Scheduled', 'Offered', 'Hired', 'Rejection Due to Salary Issue', 'Hold', 'Blacklisted', 'Technically Rejected']);
            $table->timestamps();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
