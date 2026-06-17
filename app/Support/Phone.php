<?php

namespace App\Support;

use Closure;
use Filament\Forms\Components\TextInput;

/**
 * Единое представление поля телефона во всех формах админки:
 * маска +7 (999) 999-99-99 и одно правило валидации, чтобы поведение
 * не расходилось от ресурса к ресурсу.
 */
class Phone
{
    public const MASK = '+7 (999) 999-99-99';

    public const PLACEHOLDER = '+7 (123) 456-78-90';

    /**
     * Правило: ровно 11 цифр, номер начинается с 7 или 8.
     * Пустое значение пропускаем — обязательность задаёт отдельно ->required().
     * Не используем ->tel() и жёсткий regex по символам: они не принимают
     * наш формат маски (например, +79616912848 после форматирования).
     */
    public static function rule(): Closure
    {
        return fn () => function (string $attribute, $value, Closure $fail) {
            if ($value === null || $value === '') {
                return;
            }

            $digits = preg_replace('/\D/', '', (string) $value);

            if (strlen($digits) !== 11 || ! in_array($digits[0], ['7', '8'], true)) {
                $fail('Введите корректный номер: +7 (___) ___-__-__');
            }
        };
    }

    /**
     * Навешивает на поле общую конфигурацию телефона (маску, плейсхолдер,
     * мобильную клавиатуру и правило). Метку, ->required()/->nullable() и
     * ->unique() задаёт вызывающая форма.
     */
    public static function configure(TextInput $field): TextInput
    {
        return $field
            ->mask(self::MASK)
            ->placeholder(self::PLACEHOLDER)
            ->extraInputAttributes(['inputmode' => 'tel'])
            ->rules([self::rule()]);
    }
}
