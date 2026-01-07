<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'last_followup_at')) {
                $table->timestamp('last_followup_at')->nullable()->after('status_date');
            }
        });
    }

    public function down(): void {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'last_followup_at')) {
                $table->dropColumn('last_followup_at');
            }
        });
    }
};
