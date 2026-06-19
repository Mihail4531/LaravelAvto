<?php

namespace App\Filament\Resources\Appointments\Tables;

use App\Models\Appointment;
use App\Models\Car;
use App\Models\Client;
use App\Models\Order;
use App\Support\BranchScope;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('client_name')
                    ->label('Клиент')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client_phone')
                    ->label('Телефон')
                    ->searchable(),
                TextColumn::make('branch.name')
                    ->label('Филиал')
                    ->sortable()
                    ->visible(fn () => BranchScope::shouldShowBranchUi()),
                TextColumn::make('timeSlot.starts_at')
                    ->label('Дата и время')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('carBrand.name')
                    ->label('Марка')
                    ->sortable(),
                TextColumn::make('carModel.name')
                    ->label('Модель')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Appointment::statuses()[$state] ?? $state)
                    ->color(fn ($state) => Appointment::statusColor($state))
                    ->icon(fn ($state) => Appointment::statusIcon($state)),
                TextColumn::make('processedBy.name')
                    ->label('Обработал')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('order.id')
                    ->label('Заказ №')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Филиал')
                    ->relationship('branch', 'name')
                    ->visible(fn () => BranchScope::shouldShowBranchUi()),
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(Appointment::statuses()),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn () => route('reports.appointments', [
                        'from' => now()->startOfMonth()->format('Y-m-d'),
                        'to' => now()->format('Y-m-d'),
                    ]))
                    ->openUrlInNewTab(),
            ])
            ->recordActions([
                EditAction::make()->label('Редактировать'),
                Action::make('convertToOrder')
                    ->label('Преобразовать в заказ')
                    ->icon('heroicon-o-shopping-cart')
                    ->color('success')
                    ->visible(fn (Appointment $record) => in_array($record->status, ['new', 'confirmed']))
                    ->requiresConfirmation()
                    ->modalHeading('Преобразование заявки в заказ')
                    ->modalDescription('Будет создан клиент (если не найден), автомобиль и заказ-наряд. Выбранные услуги будут перенесены.')
                    ->action(function (Appointment $record) {
                        DB::beginTransaction();
                        try {
                            // 1. Найти или создать клиента.
                            // Email теперь обязателен в форме записи — заявка без него
                            // не должна доходить до этапа. Старые заявки без email
                            // прерываем с понятным сообщением — админу нужно дозаполнить.
                            if (empty($record->client_email)) {
                                throw new \RuntimeException(
                                    'У заявки не указан email клиента. Откройте заявку, заполните поле «Email клиента» и повторите.'
                                );
                            }

                            $email = mb_strtolower(trim($record->client_email));

                            // Ищем существующего клиента по email (уникальный) ИЛИ телефону,
                            // ВКЛЮЧАЯ удалённых: unique-индексы БД сохраняются и на мягко
                            // удалённой строке, поэтому без withTrashed() create() упёрся бы
                            // в дубликат. Найденного в корзине — восстанавливаем.
                            $client = Client::withTrashed()
                                ->where(fn ($q) => $q->where('email', $email)->orWhere('phone', $record->client_phone))
                                ->first();

                            if ($client && $client->trashed()) {
                                $client->restore();
                            }

                            if (! $client) {
                                $parts = preg_split('/\s+/', trim($record->client_name), 3);
                                $client = Client::create([
                                    'phone' => $record->client_phone,
                                    'last_name' => $parts[0] ?? '',
                                    'first_name' => $parts[1] ?? '',
                                    'middle_name' => $parts[2] ?? '',
                                    'email' => $email,
                                ]);
                            }

                            // 2. Найти или создать автомобиль (привязанный к клиенту)
                            $car = Car::firstOrCreate(
                                [
                                    'client_id' => $client->id,
                                    'car_brand_id' => $record->car_brand_id,
                                    'car_model_id' => $record->car_model_id,
                                ],
                                [
                                    'vin' => null,
                                ]
                            );

                            // 3. Создать заказ-наряд
                            $order = Order::create([
                                'branch_id' => $record->branch_id,
                                'client_id' => $client->id,
                                'car_id' => $car->id,
                                'receiver_id' => Auth::id(),
                                'status' => 'new',
                                'total_amount' => 0,
                            ]);

                            // 4. Скопировать услуги из заявки в заказ
                            $services = $record->services;
                            foreach ($services as $service) {
                                $order->services()->attach($service->id, [
                                    'executor_id' => null, // позже назначит мастер
                                    'quantity' => 1,
                                    'price' => $service->price,
                                    'sum' => $service->price,
                                    'status' => 'pending',
                                ]);
                            }
                            $order->recalculateTotal();

                            // 5. Обновить заявку
                            $record->update([
                                'status' => 'converted',
                                'order_id' => $order->id,
                                'processed_by' => Auth::id(),
                                'processed_at' => now(),
                            ]);

                            DB::commit();

                            Notification::make()
                                ->success()
                                ->title('Заявка преобразована в заказ №'.$order->id)
                                ->send();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Notification::make()
                                ->danger()
                                ->title('Ошибка')
                                ->body('Не удалось преобразовать заявку: '.$e->getMessage())
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Удалить выбранные'),
                ]),
            ]);
    }
}
