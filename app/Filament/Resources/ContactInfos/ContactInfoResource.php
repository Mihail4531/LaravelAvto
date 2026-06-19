<?php

namespace App\Filament\Resources\ContactInfos;

use App\Filament\Resources\ContactInfos\Pages\ListContactInfos;
use App\Filament\Resources\ContactInfos\Schemas\ContactInfoForm;
use App\Filament\Resources\ContactInfos\Tables\ContactInfosTable;
use App\Filament\Traits\HiddenFromSidebarNav;
use App\Filament\Traits\ResourcePermissions;
use App\Models\ContactInfo;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ContactInfoResource extends Resource
{
    use HiddenFromSidebarNav;
    use ResourcePermissions;

    protected static ?string $model = ContactInfo::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-phone';

    protected static ?string $navigationLabel = 'Контактная информация';

    protected static ?string $modelLabel = 'контактную информацию';

    protected static ?string $pluralModelLabel = 'Контактная информация';

    protected static string|UnitEnum|null $navigationGroup = 'Настройки';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'phone';

    public static function form(Schema $schema): Schema
    {
        return ContactInfoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContactInfosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Singleton: создавать вторую запись нельзя (если первая уже есть),
     * удалять — тоже. Правится только существующая единственная запись.
     */
    public static function canCreate(): bool
    {
        return ! ContactInfo::query()->exists()
            && static::userCan('create_'.static::permissionKey());
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactInfos::route('/'),
        ];
    }
}
