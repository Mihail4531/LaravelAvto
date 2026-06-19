<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Контактная информация сайта — единая запись (singleton). Управляется из
     * админки (раздел «Организация» → «Контактная информация») и выводится в
     * футере публичного сайта. Раньше контакты были захардкожены в шаблоне.
     */
    public function up(): void
    {
        Schema::create('contact_infos', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->nullable();             // основной телефон
            $table->string('email')->nullable();             // email
            $table->string('working_hours')->nullable();     // часы работы
            $table->string('address')->nullable();           // общий адрес/офис
            $table->string('whatsapp')->nullable();          // ссылка/номер WhatsApp
            $table->string('telegram')->nullable();          // ссылка/ник Telegram
            $table->string('vk')->nullable();                // ссылка ВКонтакте
            $table->timestamps();
        });

        // Засеваем единственную запись текущими значениями с сайта.
        DB::table('contact_infos')->insert([
            'phone' => '+7 (961) 691-30-23',
            'email' => 'mobileoneavto@mail.ru',
            'working_hours' => 'Пн–Сб 9:00–21:00',
            'address' => null,
            'whatsapp' => null,
            'telegram' => null,
            'vk' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_infos');
    }
};
