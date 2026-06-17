<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Заполняем NULL/пустые email placeholder'ом по id клиента.
        //    Это нужно потому что NOT NULL не примет существующие пустые значения.
        //    Админ должен будет заполнить настоящие email через панель.
        DB::table('clients')
            ->where(function ($q) {
                $q->whereNull('email')->orWhere('email', '');
            })
            ->get()
            ->each(function ($client) {
                DB::table('clients')
                    ->where('id', $client->id)
                    ->update(['email' => "client_{$client->id}@placeholder.local"]);
            });

        // 2. Делаем email NOT NULL + UNIQUE.
        Schema::table('clients', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
            $table->unique('email');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->string('email')->nullable()->change();
        });
    }
};
