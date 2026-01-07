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
        Schema::table('payment_accounts', function (Blueprint $table) {
            $table->dropColumn(['swift_code', 'currency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('payment_accounts', function (Blueprint $table) {
            $table->string('swift_code', 20)->nullable();
            $table->string('currency', 10)->nullable();
        });
    }
};
