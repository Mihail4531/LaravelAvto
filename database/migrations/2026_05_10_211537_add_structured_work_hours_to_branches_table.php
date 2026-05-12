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
      Schema::table('branches', function (Blueprint $table) {
    $table->enum('work_days_start', ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'])->default('monday')->after('work_hours');
    $table->enum('work_days_end', ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'])->default('saturday')->after('work_days_start');
    $table->time('work_time_start')->default('09:00')->after('work_days_end');
    $table->time('work_time_end')->default('21:00')->after('work_time_start');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            //
        });
    }
};
