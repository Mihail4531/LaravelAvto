<?php

namespace App\Filament\Resources\Cars;

use App\Filament\Resources\Cars\Pages\CreateCar;
use App\Filament\Resources\Cars\Pages\EditCar;
use App\Filament\Resources\Cars\Pages\ListCars;
use App\Filament\Resources\Cars\Schemas\CarForm;
use App\Filament\Resources\Cars\Tables\CarsTable;
use App\Filament\Traits\ResourcePermissions;
use App\Models\Car;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CarResource extends Resource
{
    use ResourcePermissions;

    protected static ?string $model = Car::class;

    // Иконка
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    // Название в меню
    protected static ?string $navigationLabel = 'Автомобили';

    // Единственное число
    protected static ?string $modelLabel = 'автомобиль';

    // Множественное число
    protected static ?string $pluralModelLabel = 'Автомобили клиентов';

    // Группа в меню
    protected static string|UnitEnum|null $navigationGroup = 'Работа';

    // Сортировка в группе (после клиентов)
    protected static ?int $navigationSort = 5;

    // Поле для заголовка записи (используется в селектах, хлебных крошках)
    protected static ?string $recordTitleAttribute = 'vin';

    public static function form(Schema $schema): Schema
    {
        return CarForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CarsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCars::route('/'),
            'create' => CreateCar::route('/create'),
            'edit' => EditCar::route('/{record}/edit'),
        ];
    }

    // Поддержка мягкого удаления: показываем в том числе удалённые записи (для вкладок)
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withTrashed();
    }

    // Для корректной загрузки записи при редактировании (если удалена)
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
