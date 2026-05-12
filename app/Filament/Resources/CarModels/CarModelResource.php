<?php

namespace App\Filament\Resources\CarModels;
use Illuminate\Database\Eloquent\Builder;

use App\Filament\Resources\CarModels\Pages\CreateCarModel;
use App\Filament\Resources\CarModels\Pages\EditCarModel;
use App\Filament\Resources\CarModels\Pages\ListCarModels;
use App\Filament\Resources\CarModels\Schemas\CarModelForm;
use App\Filament\Resources\CarModels\Tables\CarModelsTable;
use App\Models\CarModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CarModelResource extends Resource
{
    protected static ?string $model = CarModel::class;

    // Иконка для модели
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    // Заголовок в меню
    protected static ?string $navigationLabel = 'Модели авто';

    // Единственное число
    protected static ?string $modelLabel = 'модель';

    // Множественное число
    protected static ?string $pluralModelLabel = 'Модели автомобилей';

    // Группа в меню
    protected static string|UnitEnum|null $navigationGroup = 'Автомобили';

    // Сортировка внутри группы (марки – 1, модели – 2)
    protected static ?int $navigationSort = 2;

    // Поле для заголовка записи (используется в селектах)
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CarModelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CarModelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()->withTrashed();
}
    public static function getPages(): array
    {
        return [
            'index' => ListCarModels::route('/'),
            'create' => CreateCarModel::route('/create'),
            'edit' => EditCarModel::route('/{record}/edit'),
        ];
    }
}
