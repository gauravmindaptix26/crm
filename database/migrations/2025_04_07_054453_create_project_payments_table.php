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
        Schema::create('project_payments', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
        
            $table->unsignedBigInteger('account_id')->nullable();
            $table->foreign('account_id')->references('id')->on('payment_accounts')->onDelete('set null');
        
            $table->decimal('payment_amount', 10, 2);
            $table->decimal('commission_amount', 10, 2)->nullable();
            $table->date('payment_month');
            $table->text('payment_details')->nullable();
            $table->string('screenshot')->nullable();
            
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_payments');
    }
};
