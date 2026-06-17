<?php

namespace App\Filament\Concerns;

use Filament\Actions\Action;

/**
 * Единые подписи кнопок сохранения на страницах создания: «Добавить» вместо
 * дефолтного «Создать». Заголовок страницы (getTitle) каждая страница задаёт
 * сама — из-за падежей в русском («Добавить услугу», «Добавить марку» и т.п.).
 */
trait AddButtonLabels
{
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Добавить');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()->label('Добавить и ещё');
    }
}
