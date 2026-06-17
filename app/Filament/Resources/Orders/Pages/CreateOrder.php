<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    // Для заказ-наряда оставляем «Создать» (он формируется/оформляется,
    // а не добавляется как справочная запись) — кнопка сохранения и заголовок
    // используют дефолтные «Создать»/«Создание», поэтому трейт AddButtonLabels
    // здесь намеренно не подключаем.
    public function getTitle(): string
    {
        return 'Создание заказ-наряда';
    }

    /**
     * Строки услуг из формы создания (поле-репитер service_lines).
     * Это не колонка модели — вынимаем их до создания заказа и
     * привязываем к pivot order_service уже после (см. afterCreate).
     *
     * @var array<int, array<string, mixed>>
     */
    protected array $serviceLines = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->serviceLines = $data['service_lines'] ?? [];
        unset($data['service_lines']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $order = $this->record;
        $attached = false;

        foreach ($this->serviceLines as $line) {
            if (empty($line['service_id'])) {
                continue;
            }

            $quantity = max(1, (int) ($line['quantity'] ?? 1));
            $price = (float) ($line['price'] ?? 0);

            $order->services()->attach($line['service_id'], [
                'executor_id' => null, // мастера назначат позже (assign_order_executor)
                'quantity' => $quantity,
                'price' => $price,
                'sum' => round($quantity * $price, 2),
                'status' => 'pending',
            ]);
            $attached = true;
        }

        if ($attached) {
            $order->recalculateTotal();
        }
    }
}
