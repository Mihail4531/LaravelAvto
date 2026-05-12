<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Models\Order;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('order_id')
                    ->label('Заказ-наряд')
                    ->options(
                        Order::with('client')
                            ->whereIn('status', ['completed', 'in_progress'])
                            ->get()
                            ->mapWithKeys(fn ($o) => [
                                $o->id => '№' . $o->id . ' — ' . ($o->client?->full_name ?? 'Клиент не указан'),
                            ])
                    )
                    ->searchable()
                    ->required()
                    ->columnSpan(2),

                Select::make('cashier_id')
                    ->label('Кассир / приёмщик')
                    ->options(User::where('active', true)->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Select::make('method')
                    ->label('Способ оплаты')
                    ->options([
                        'cash'     => 'Наличные',
                        'card'     => 'Карта',
                        'transfer' => 'Перевод',
                    ])
                    ->required(),

                TextInput::make('amount')
                    ->label('Сумма (₽)')
                    ->required()
                    ->numeric()
                    ->minValue(0.01)
                    ->prefix('₽'),

                DateTimePicker::make('paid_at')
                    ->label('Дата и время оплаты')
                    ->required()
                    ->default(now()),

                Textarea::make('comment')
                    ->label('Комментарий')
                    ->rows(2)
                    ->nullable()
                    ->columnSpan(2),
            ]);
    }
}
