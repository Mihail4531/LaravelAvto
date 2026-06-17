<?php

namespace App\Filament\Resources\Services;

use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Filament\Resources\Services\Schemas\ServiceForm;
use App\Filament\Resources\Services\Tables\ServicesTable;
use App\Filament\Traits\HiddenFromSidebarNav;
use App\Filament\Traits\ResourcePermissions;
use App\Models\Service;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ServiceResource extends Resource
{
    use HiddenFromSidebarNav;
    use ResourcePermissions;

    protected static ?string $model = Service::class;

    // Иконка в меню
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wrench';

    // Название в меню
    protected static ?string $navigationLabel = 'Услуги';

    // Единственное число
    protected static ?string $modelLabel = 'услугу';

    // Множественное число
    protected static ?string $pluralModelLabel = 'Услуги';

    // Группа в меню
    protected static string|UnitEnum|null $navigationGroup = 'Настройки';

    // Порядок сортировки внутри группы
    protected static ?int $navigationSort = 1;

    // Поле для заголовка записи
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }
}
