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
        Schema::table('project_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_amount')->change();
            $table->unsignedBigInteger('commission_amount')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('project_payments', function (Blueprint $table) {
            $table->decimal('payment_amount', 10, 2)->change();
            $table->decimal('commission_amount', 10, 2)->nullable()->change();
        });
    }
};
