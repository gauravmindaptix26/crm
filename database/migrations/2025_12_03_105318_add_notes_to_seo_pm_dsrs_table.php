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
        Schema::table('seo_pm_dsrs', function (Blueprint $table) {
            $table->text('case_study_description')->nullable()->after('updated_case_study');
            $table->text('payment_notes')->nullable()->after('payment_followups');
            $table->text('happy_things_notes')->nullable()->after('closed_today');
        });
    }
    
    public function down()
    {
        Schema::table('seo_pm_dsrs', function (Blueprint $table) {
            $table->dropColumn(['case_study_description', 'payment_notes', 'happy_things_notes']);
        });
    }
    
};
