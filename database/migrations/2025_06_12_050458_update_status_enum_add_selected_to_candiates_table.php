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
        DB::statement("ALTER TABLE candidates MODIFY status ENUM(
            'Shortlist', 
            'Scheduled', 
            'Offered', 
            'Hired', 
            'Rejection Due to Salary Issue', 
            'Hold', 
            'Blacklisted', 
            'Technically Rejected',
            'Selected'
        ) NOT NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE candidates MODIFY status ENUM(
            'Shortlist', 
            'Scheduled', 
            'Offered', 
            'Hired', 
            'Rejection Due to Salary Issue', 
            'Hold', 
            'Blacklisted', 
            'Technically Rejected'
        ) NOT NULL");
    }
};
