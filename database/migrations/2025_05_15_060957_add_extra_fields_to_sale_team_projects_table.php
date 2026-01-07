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
        Schema::table('sale_team_projects', function (Blueprint $table) {
            $table->text('website_dev_commitment')->nullable();
            $table->text('internal_explainer_video')->nullable();
            $table->text('content_commitment')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('sale_team_projects', function (Blueprint $table) {
            $table->dropColumn(['website_dev_commitment', 'internal_explainer_video', 'content_commitment']);
        });
    }
};
