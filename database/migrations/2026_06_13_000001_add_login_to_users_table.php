<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Логин сотрудника — основной идентификатор для входа в АИС
        // (вход по логину удобнее для персонала, чем по email).
        Schema::table('users', function (Blueprint $table) {
            $table->string('login')->nullable()->after('name');
        });

        // Заполняем логин существующим пользователям из локальной части email,
        // гарантируя уникальность (при коллизии добавляем id).
        $used = [];
        foreach (DB::table('users')->select('id', 'email')->get() as $user) {
            $base = Str::slug(Str::before((string) $user->email, '@'), '_') ?: 'user';
            $login = $base;

            if (isset($used[$login])) {
                $login = $base.'_'.$user->id;
            }

            $used[$login] = true;
            DB::table('users')->where('id', $user->id)->update(['login' => $login]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('login');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['login']);
            $table->dropColumn('login');
        });
    }
};
