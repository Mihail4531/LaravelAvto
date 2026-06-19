<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactInfo extends Model
{
    protected $fillable = [
        'phone', 'email', 'working_hours', 'address',
        'whatsapp', 'telegram', 'vk',
    ];

    /**
     * Единственная запись контактов (singleton). Создаётся при первом
     * обращении, если её ещё нет. Используется и в админке, и в шаблонах сайта.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }

    /**
     * Готовая ссылка для атрибута href="tel:" — только + и цифры.
     * Null, если телефон не задан (чтобы в шаблоне скрыть блок).
     */
    public function telHref(): ?string
    {
        return $this->phone
            ? 'tel:'.preg_replace('/[^+\d]/', '', $this->phone)
            : null;
    }
}
