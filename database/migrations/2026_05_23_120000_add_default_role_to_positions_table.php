<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            // Slug Spatie-роли, которая выдаётся пользователям с этой должностью.
            // NULL означает "должность без доступа в АИС" (уборщик, разнорабочий).
            $table->string('default_role')->nullable()->after('hourly_rate');
        });
    }

    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropColumn('default_role');
        });
    }
};
