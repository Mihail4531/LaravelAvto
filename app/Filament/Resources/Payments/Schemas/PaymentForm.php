<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Models\Order;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('order_id')
                    ->label('Заказ-наряд')
                    ->options(function (?Model $record) {
                        // Показываем только заказы, которые ещё не оплачены полностью.
                        // При редактировании текущий платёж не учитываем, чтобы свой
                        // заказ оставался в списке даже если он закрыт этим платежом.
                        return Order::with('client')
                            ->whereIn('status', ['completed', 'in_progress', 'new'])
                            ->get()
                            ->filter(function ($order) use ($record) {
                                $paid = $order->payments()
                                    ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                    ->sum('amount');

                                return ((float) $order->total_amount - (float) $paid) > 0.005;
                            })
                            ->mapWithKeys(fn ($o) => [
                                $o->id => sprintf(
                                    '№%d — %s',
                                    $o->id,
                                    $o->client?->full_name ?? 'Клиент не указан',
                                ),
                            ]);
                    })
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (callable $set, callable $get) {
                        $orderId = $get('order_id');
                        if (! $orderId) {
                            return;
                        }
                        $order = Order::find($orderId);
                        if (! $order) {
                            return;
                        }

                        // Подставляем приёмщика, ведущего этот заказ.
                        if ($order->receiver_id) {
                            $set('cashier_id', $order->receiver_id);
                        }

                        // Подставляем полную сумму заказа. Поле редактируемое —
                        // кассир правит вручную (напр. при частичной оплате).
                        $set('amount', round((float) $order->total_amount, 2));
                    })
                    ->columnSpan(2),

                Select::make('cashier_id')
                    ->label('Приёмщик')
                    ->options(function (?Model $record, callable $get) {
                        $users = User::where('active', true)
                            ->permission('create_payment')
                            ->withoutSuperAdmin()
                            ->with('position')
                            ->orderBy('name')
                            ->get();

                        // Текущее значение поля (подставленный приёмщик заказа, либо
                        // тот, кто оформляет платёж) всегда должно быть в списке — иначе
                        // при сотруднике вне выборки (приёмщик заказа без права на кассу,
                        // технический/уволенный) поле показало бы голый ID вместо имени.
                        $currentId = $get('cashier_id') ?: ($record?->cashier_id ?? Auth::id());
                        if ($currentId && ! $users->contains('id', $currentId)) {
                            if ($current = User::with('position')->find($currentId)) {
                                $users->push($current);
                            }
                        }

                        return $users->mapWithKeys(fn (User $u) => [
                            $u->id => $u->name.($u->position ? ' — '.$u->position->name : ''),
                        ]);
                    })
                    ->default(Auth::id())
                    ->searchable()
                    ->required()
                    ->helperText('Подставляется приёмщик заказа. Можно изменить, если оплату принимает другой сотрудник.'),

                Select::make('method')
                    ->label('Способ оплаты')
                    ->options([
                        'cash' => 'Наличные',
                        'card' => 'Карта',
                        'transfer' => 'Перевод',
                    ])
                    ->required(),

                TextInput::make('amount')
                    ->label('Сумма (₽)')
                    ->required()
                    ->numeric()
                    ->minValue(0.01)
                    ->prefix('₽')
                    ->helperText('Подставляется сумма заказа. Можно изменить — например, при частичной оплате.'),

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
