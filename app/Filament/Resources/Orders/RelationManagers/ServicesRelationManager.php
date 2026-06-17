<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'services';

    protected static ?string $title = 'Услуги';

    protected static ?string $modelLabel = 'услугу';

    protected static ?string $pluralModelLabel = 'Услуги';

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Кандидаты в исполнители — активные сотрудники, которым разрешено вести
     * работы по услуге (право change_own_service_status). Не привязано к имени
     * роли: если управляющий выдаст это право другой роли в админке, её
     * сотрудники сразу станут доступны для назначения.
     *
     * @return array<int, string>
     */
    protected static function mechanicOptions(): array
    {
        return User::where('active', true)
            ->permission('change_own_service_status')
            ->withoutSuperAdmin()
            ->with('position')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (User $u) => [
                $u->id => $u->name.($u->position ? ' — '.$u->position->name : ''),
            ])
            ->all();
    }

    public function form(Schema $schema): Schema
    {
        // Используется только при редактировании уже прикреплённой услуги.
        // Сам Service не меняем — только pivot-поля.
        return $schema
            ->components([
                Select::make('executor_id')
                    ->label('Исполнитель (мастер)')
                    ->options(static::mechanicOptions())
                    ->placeholder('Назначить мастера')
                    ->searchable()
                    ->nullable()
                    ->visible(fn () => auth()->user()?->can('assign_order_executor')),

                TextInput::make('quantity')
                    ->label('Количество')
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->required()
                    ->live(debounce: 300)
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $price = $get('price') ?? 0;
                        $set('sum', round((float) $state * (float) $price, 2));
                    }),

                TextInput::make('price')
                    ->label('Цена за ед. (₽)')
                    ->numeric()
                    ->minValue(0)
                    ->prefix('₽')
                    ->required()
                    ->live(debounce: 300)
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $qty = $get('quantity') ?? 1;
                        $set('sum', round((float) $state * (float) $qty, 2));
                    }),

                TextInput::make('sum')
                    ->label('Сумма (₽)')
                    ->numeric()
                    ->prefix('₽')
                    ->disabled()
                    ->dehydrated(true)
                    ->default(0),

                Select::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'Ожидает',
                        'in_progress' => 'В работе',
                        'done' => 'Выполнено',
                    ])
                    ->default('pending')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Услуга')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('pivot.executor_id')
                    ->label('Исполнитель (мастер)')
                    ->formatStateUsing(function ($state) {
                        if (! $state) {
                            return '— не назначен';
                        }
                        $u = User::with('position')->find($state);
                        if (! $u) {
                            return '—';
                        }

                        return $u->name.($u->position ? ' · '.$u->position->name : '');
                    })
                    ->color(fn ($state) => $state ? null : 'gray')
                    ->wrap(),

                TextColumn::make('pivot.quantity')
                    ->label('Кол-во')
                    ->numeric(),

                TextColumn::make('pivot.price')
                    ->label('Цена (₽)')
                    ->money('RUB'),

                TextColumn::make('pivot.sum')
                    ->label('Сумма (₽)')
                    ->money('RUB'),

                BadgeColumn::make('pivot.status')
                    ->label('Статус')
                    ->colors([
                        'gray' => 'pending',
                        'warning' => 'in_progress',
                        'success' => 'done',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Ожидает',
                        'in_progress' => 'В работе',
                        'done' => 'Выполнено',
                        default => $state,
                    }),
            ])
            ->recordActions([
                // Механик отмечает статус ТОЛЬКО своей услуги (change_own_service_status)
                Action::make('markServiceStatus')
                    ->label('Моя работа')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('primary')
                    ->visible(fn (Model $record) => $this->getOwnerRecord()->isOpen()
                        && auth()->user()?->can('change_own_service_status')
                        && (int) $record->pivot->executor_id === (int) auth()->id())
                    ->modalHeading('Статус вашей работы по услуге')
                    ->modalSubmitActionLabel('Сохранить')
                    ->fillForm(fn (Model $record): array => ['status' => $record->pivot->status])
                    ->schema([
                        Select::make('status')
                            ->label('Статус')
                            ->options([
                                'pending' => 'Ожидает',
                                'in_progress' => 'В работе',
                                'done' => 'Выполнено',
                            ])
                            ->required(),
                    ])
                    ->action(fn (Model $record, array $data) => $record->pivot->update(['status' => $data['status']])),

                Action::make('assignExecutor')
                    ->label('Назначить мастера')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->visible(fn () => $this->getOwnerRecord()->isOpen()
                        && auth()->user()?->can('assign_order_executor'))
                    ->modalHeading('Назначение мастера на услугу')
                    ->modalSubmitActionLabel('Назначить')
                    ->fillForm(fn (Model $record): array => [
                        'executor_id' => $record->pivot->executor_id,
                    ])
                    ->schema([
                        Select::make('executor_id')
                            ->label('Мастер')
                            ->options(static::mechanicOptions())
                            ->placeholder('Не назначен')
                            ->searchable()
                            ->nullable()
                            ->helperText('В списке — активные мастера с их специализацией.'),
                    ])
                    ->action(function (Model $record, array $data): void {
                        $record->pivot->update(['executor_id' => $data['executor_id'] ?? null]);
                    }),

                EditAction::make()
                    ->label('Изменить')
                    ->visible(fn () => $this->getOwnerRecord()->isOpen())
                    ->fillForm(fn (Model $record): array => [
                        'executor_id' => $record->pivot->executor_id,
                        'quantity' => $record->pivot->quantity,
                        'price' => $record->pivot->price,
                        'sum' => $record->pivot->sum,
                        'status' => $record->pivot->status,
                    ])
                    ->using(function (Model $record, array $data): Model {
                        $record->pivot->update([
                            'executor_id' => $data['executor_id'] ?? null,
                            'quantity' => $data['quantity'],
                            'price' => $data['price'],
                            'sum' => $data['sum'],
                            'status' => $data['status'],
                        ]);
                        $this->getOwnerRecord()->load('services', 'parts');
                        $this->getOwnerRecord()->recalculateTotal();

                        return $record;
                    }),

                DeleteAction::make()
                    ->label('Удалить')
                    ->visible(fn () => $this->getOwnerRecord()->isOpen())
                    ->after(function () {
                        $this->getOwnerRecord()->load('services', 'parts');
                        $this->getOwnerRecord()->recalculateTotal();
                    }),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Добавить услугу')
                    ->visible(fn () => $this->getOwnerRecord()->isOpen())
                    ->preloadRecordSelect()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('Услуга')
                            ->required(),

                        Select::make('executor_id')
                            ->label('Исполнитель (мастер)')
                            ->options(static::mechanicOptions())
                            ->placeholder('Назначить мастера')
                            ->searchable()
                            ->nullable()
                            ->visible(fn () => auth()->user()?->can('assign_order_executor')),

                        TextInput::make('quantity')
                            ->label('Количество')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),

                        TextInput::make('price')
                            ->label('Цена за ед. (₽)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('₽')
                            ->required(),

                        TextInput::make('sum')
                            ->label('Сумма (₽)')
                            ->numeric()
                            ->prefix('₽')
                            ->default(0),

                        Select::make('status')
                            ->label('Статус')
                            ->options([
                                'pending' => 'Ожидает',
                                'in_progress' => 'В работе',
                                'done' => 'Выполнено',
                            ])
                            ->default('pending')
                            ->required(),
                    ])
                    ->after(function () {
                        $this->getOwnerRecord()->load('services', 'parts');
                        $this->getOwnerRecord()->recalculateTotal();
                    }),
            ]);
    }
}
