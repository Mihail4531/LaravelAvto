<?php

namespace App\Filament\Resources\Clients;

use App\Filament\Resources\Clients\Pages\CreateClient;
use App\Filament\Resources\Clients\Pages\EditClient;
use App\Filament\Resources\Clients\Pages\ListClients;
use App\Filament\Resources\Clients\Schemas\ClientForm;
use App\Filament\Resources\Clients\Tables\ClientsTable;
use App\Models\Client;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    // Иконка в меню
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user';

    // Название в меню
    protected static ?string $navigationLabel = 'Клиенты';

    // Единственное число
    protected static ?string $modelLabel = 'клиента';

    // Множественное число
    protected static ?string $pluralModelLabel = 'Клиенты';

    // Группа в меню
    protected static string|UnitEnum|null $navigationGroup = 'Клиенты и заказы';

    // Порядок сортировки в группе
    protected static ?int $navigationSort = 1;

    // Поле для заголовка записи (используется в селектах)
    protected static ?string $recordTitleAttribute = 'phone';

    public static function form(Schema $schema): Schema
    {
        return ClientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClients::route('/'),
            'create' => CreateClient::route('/create'),
            'edit' => EditClient::route('/{record}/edit'),
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
