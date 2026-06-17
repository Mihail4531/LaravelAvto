<?php

namespace App\Filament\Resources\Branches\Schemas;

use App\Support\Phone;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(12)
                    ->schema([
                        // ЛЕВАЯ КОЛОНКА (основное)
                        Grid::make(1)
                            ->columnSpan(['default' => 12, 'xl' => 6])
                            ->schema([
                                Section::make('Основная информация')
                                    ->icon('heroicon-o-building-office')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Название филиала')
                                                    ->required()
                                                    ->live(debounce: 100)
                                                    ->afterStateUpdated(fn ($state, callable $set) => $state && $set('slug', Str::slug($state)))
                                                    ->placeholder('Например: Автосервис на Ленина'),
                                                TextInput::make('slug')
                                                    ->label('Символьный код')
                                                    ->required()
                                                    ->rule('alpha_dash')
                                                    ->unique('branches', 'slug', ignorable: fn ($record) => $record)
                                                    ->validationMessages(['unique' => 'Такой символный код уже используется.'])
                                                    ->helperText('Только латиница, цифры, дефис. Авто-генерация из названия.'),
                                                TextInput::make('city')
                                                    ->label('Город')
                                                    ->required(),
                                                TextInput::make('address')
                                                    ->label('Адрес')
                                                    ->required(),
                                            ]),
                                    ]),

                                Section::make('Контакты')
                                    ->icon('heroicon-o-phone')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Phone::configure(TextInput::make('phone'))
                                                    ->label('Телефон')
                                                    ->required(),
                                                TextInput::make('email')
                                                    ->label('Email')
                                                    ->email(),
                                            ]),
                                    ]),

                                Section::make('Режим работы')
                                    ->icon('heroicon-o-clock')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('work_days_start')
                                                    ->label('Начало недели')
                                                    ->options([
                                                        'monday' => 'Понедельник',
                                                        'tuesday' => 'Вторник',
                                                        'wednesday' => 'Среда',
                                                        'thursday' => 'Четверг',
                                                        'friday' => 'Пятница',
                                                        'saturday' => 'Суббота',
                                                        'sunday' => 'Воскресенье',
                                                    ])
                                                    ->default('monday')
                                                    ->required(),
                                                Select::make('work_days_end')
                                                    ->label('Конец недели')
                                                    ->options([
                                                        'monday' => 'Понедельник',
                                                        'tuesday' => 'Вторник',
                                                        'wednesday' => 'Среда',
                                                        'thursday' => 'Четверг',
                                                        'friday' => 'Пятница',
                                                        'saturday' => 'Суббота',
                                                        'sunday' => 'Воскресенье',
                                                    ])
                                                    ->default('saturday')
                                                    ->required(),
                                                TextInput::make('work_time_start')
                                                    ->label('Время открытия')
                                                    ->type('time')
                                                    ->default('09:00')
                                                    ->required(),
                                                TextInput::make('work_time_end')
                                                    ->label('Время закрытия')
                                                    ->type('time')
                                                    ->default('21:00')
                                                    ->required(),
                                            ]),
                                    ]),
                            ]),

                        // ПРАВАЯ КОЛОНКА
                        Grid::make(1)
                            ->columnSpan(['default' => 8, 'xl' => 6])
                            ->schema([
                                Section::make('Статус')
                                    ->icon('heroicon-o-check-circle')
                                    ->schema([
                                        Toggle::make('active')
                                            ->label('Филиал активен')
                                            ->default(true),
                                    ]),

                                Section::make('Координаты')
                                    ->icon('heroicon-o-map-pin')
                                    ->description('Опционально, для отображения на карте')
                                    ->collapsible()          // сворачиваемая секция
                                    ->collapsed(true)        // по умолчанию закрыта
                                    ->schema([
                                        Grid::make(1)
                                            ->schema([
                                                TextInput::make('latitude')
                                                    ->label('Широта')
                                                    ->numeric()
                                                    ->step(0.0000001)
                                                    ->placeholder('Например: 55.751244'),
                                                TextInput::make('longitude')
                                                    ->label('Долгота')
                                                    ->numeric()
                                                    ->step(0.0000001)
                                                    ->placeholder('Например: 37.618423'),
                                            ]),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
