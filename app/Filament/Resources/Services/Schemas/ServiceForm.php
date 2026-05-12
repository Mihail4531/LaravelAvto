<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use App\Models\Category;
use Illuminate\Support\Str;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label('Категория')
                    ->options(function () {
                        // Получаем все категории с их родителями
                        $categories = Category::with('parent')->get();
                        $options = [];
                        foreach ($categories as $category) {
                            $prefix = $category->parent ? '—— ' : '';
                            $options[$category->id] = $prefix . $category->name;
                        }
                        return $options;
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->placeholder('Выберите категорию'),

                TextInput::make('name')
                    ->label('Название услуги')
                    ->required()
                    ->live(debounce: 100)
                    ->afterStateUpdated(fn ($state, callable $set) => $state && $set('slug', Str::slug($state)))
                    ->placeholder('Например: Замена масла'),

                TextInput::make('slug')
                    ->label('Символьный код')
                    ->required()
                    ->rule('alpha_dash')
                    ->unique('services', 'slug', ignorable: fn ($record) => $record)
                    ->maxLength(255)
                    ->validationMessages(['unique' => 'Такой символьный код уже используется.'])
                    ->helperText('Только латиница, цифры, дефис. Генерируется автоматически, но можно изменить.'),

                Textarea::make('description')
                    ->label('Описание')
                    ->rows(3)
                    ->nullable(),

                TextInput::make('duration_minutes')
                    ->label('Длительность (минуты)')
                    ->numeric()
                    ->minValue(1)
                    ->nullable()
                    ->placeholder('Например: 30'),

                TextInput::make('price')
                    ->label('Цена (₽)')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->prefix('₽'),

                FileUpload::make('image')
                    ->label('Изображение услуги')
                    ->image()
                    ->directory('services')
                    ->visibility('public')
                    ->nullable(),

                TextInput::make('sort_order')
                    ->label('Порядок сортировки')
                    ->numeric()
                    ->default(0)
                    ->helperText('Чем меньше число, тем выше услуга в списке.'),

                Toggle::make('active')
                    ->label('Активна')
                    ->default(true),
            ]);
    }
}
