<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Удаляем лишние поля
            $table->dropColumn(['type', 'company_name', 'address']);
            // Делаем телефон уникальным
            $table->unique('phone');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->enum('type', ['individual', 'company'])->default('individual')->after('id');
            $table->string('company_name')->nullable()->after('middle_name');
            $table->string('address')->nullable()->after('email');
        });
    }
};
