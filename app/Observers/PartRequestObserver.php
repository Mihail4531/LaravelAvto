<?php

namespace App\Observers;

use App\Models\PartRequest;

class PartRequestObserver
{
    /**
     * Раньше тут уведомляли кладовщика о новой заявке на подтверждение.
     * Теперь запчасть выдаётся сразу при создании (самовыдача, без
     * подтверждения), поэтому отдельное уведомление не нужно. Оповещение о
     * низком остатке по-прежнему шлёт PartObserver при списании со склада.
     */
    public function created(PartRequest $request): void
    {
        // no-op
    }
}
