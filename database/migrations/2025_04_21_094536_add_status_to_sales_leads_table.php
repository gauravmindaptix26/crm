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
        Schema::table('sales_leads', function (Blueprint $table) {
            $table->enum('status', ['Bid', 'Progress', 'Hired'])->nullable();
            $table->date('status_update_date')->nullable();
            $table->text('status_reason')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('sales_leads', function (Blueprint $table) {
            $table->dropColumn(['status', 'status_update_date', 'status_reason']);
        });
    }
};
