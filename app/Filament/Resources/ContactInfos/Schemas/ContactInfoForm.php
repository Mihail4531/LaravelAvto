<?php

namespace App\Filament\Resources\ContactInfos\Schemas;

use App\Support\Phone;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactInfoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основные контакты')
                    ->description('Выводятся в футере сайта.')
                    ->icon('heroicon-o-phone')
                    ->columns(2)
                    ->schema([
                        // Единый телефон проекта: маска +7 (999) 999-99-99 и
                        // правило «11 цифр, начинается с 7/8» (App\Support\Phone).
                        Phone::configure(TextInput::make('phone'))
                            ->label('Телефон'),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->placeholder('mobileoneavto@mail.ru')
                            ->maxLength(255),

                        TextInput::make('working_hours')
                            ->label('Часы работы')
                            ->placeholder('Пн–Сб 9:00–21:00')
                            ->maxLength(255),

                        TextInput::make('address')
                            ->label('Адрес (общий)')
                            ->placeholder('Необязательно — адреса филиалов задаются отдельно')
                            ->maxLength(255),
                    ]),

                Section::make('Соцсети и мессенджеры')
                    ->description('Ссылки или номера. Пустые поля на сайте не показываются.')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextInput::make('whatsapp')
                            ->label('WhatsApp')
                            ->placeholder('https://wa.me/79616913023')
                            ->maxLength(255),

                        TextInput::make('telegram')
                            ->label('Telegram')
                            ->placeholder('https://t.me/username')
                            ->maxLength(255),

                        TextInput::make('vk')
                            ->label('ВКонтакте')
                            ->placeholder('https://vk.com/username')
                            ->maxLength(255),
                    ]),
            ]);
    }
}
