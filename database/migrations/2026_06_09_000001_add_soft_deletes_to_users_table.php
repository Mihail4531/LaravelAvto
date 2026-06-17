<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Сотрудники связаны историчными внешними ключами (orders.receiver_id,
     * order_service.executor_id, payments.cashier_id), поэтому жёсткое удаление
     * нарушает целостность. Включаем мягкое удаление: «удалённый» сотрудник
     * исчезает из списков, но история остаётся, и его можно восстановить.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
