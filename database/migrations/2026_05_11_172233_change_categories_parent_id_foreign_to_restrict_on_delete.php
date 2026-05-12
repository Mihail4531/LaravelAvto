<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Удаляем старый внешний ключ
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
        });

        // Добавляем новый с restrictOnDelete()
        Schema::table('categories', function (Blueprint $table) {
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('categories')
                  ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('categories')
                  ->nullOnDelete();
        });
    }
};
