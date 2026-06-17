<?php

namespace App\Filament\Resources\CarBrands;

use App\Filament\Resources\CarBrands\Pages\CreateCarBrand;
use App\Filament\Resources\CarBrands\Pages\EditCarBrand;
use App\Filament\Resources\CarBrands\Pages\ListCarBrands;
use App\Filament\Resources\CarBrands\Schemas\CarBrandForm;
use App\Filament\Resources\CarBrands\Tables\CarBrandsTable;
use App\Filament\Traits\HiddenFromSidebarNav;
use App\Filament\Traits\ResourcePermissions;
use App\Models\CarBrand;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CarBrandResource extends Resource
{
    use HiddenFromSidebarNav;
    use ResourcePermissions;

    protected static ?string $model = CarBrand::class;

    // Иконка в меню
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    // Название пункта в меню
    protected static ?string $navigationLabel = 'Марки авто';

    // Единственное число
    protected static ?string $modelLabel = 'марку';

    // Множественное число
    protected static ?string $pluralModelLabel = 'Марки автомобилей';

    // Группа в меню
    protected static string|UnitEnum|null $navigationGroup = 'Настройки';

    protected static ?int $navigationSort = 3;

    // Поле для заголовка записи (используется в селектах и хлебных крошках)
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CarBrandForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CarBrandsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCarBrands::route('/'),
            'create' => CreateCarBrand::route('/create'),
            'edit' => EditCarBrand::route('/{record}/edit'),
        ];
    }

    // Важно: показывать в таблице также и мягко удалённые записи (для вкладок)
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withTrashed();
    }
}
