<?php

/**
 * Локализация permissions.
 * Ключ — значение поля `name` в таблице permissions (Spatie).
 *
 * Структура:
 *   actions     — глаголы действий для CRUD-permissions <action>_<resource>
 *   resources   — родительный падеж имени ресурса (для глаголов)
 *   custom      — отдельные permissions, не подпадающие под шаблон action_resource
 *
 * Финальная строка собирается как: "{action} {resource}".
 */
return [
    'actions' => [
        'view_any' => 'Просмотр списка',
        'view' => 'Просмотр',
        'create' => 'Создание',
        'update' => 'Редактирование',
        'delete' => 'Удаление',
    ],

    'resources' => [
        'appointment' => 'заявок',
        'order' => 'заказов',
        'client' => 'клиентов',
        'car' => 'автомобилей',
        'part' => 'запчастей',
        'payment' => 'платежей',
        'service' => 'услуг',
        'category' => 'категорий',
        'branch' => 'филиалов',
        'time_slot' => 'временных слотов',
        'user' => 'сотрудников',
        'position' => 'должностей',
        'car_brand' => 'марок авто',
        'car_model' => 'моделей авто',
        'gallery_item' => 'фото витрины',
        'role' => 'ролей',
    ],

    'custom' => [
        'assign_order_executor' => 'Назначение исполнителя на заказ',
        'change_order_status' => 'Изменение статуса заказа',
        'change_own_service_status' => 'Изменение статуса своих услуг',
        'issue_part' => 'Выдача запчастей со склада',
        'receive_part' => 'Приёмка запчастей на склад',
        'view_financial_reports' => 'Просмотр финансовых отчётов',
        'view_warehouse_reports' => 'Просмотр складских отчётов',
        'access_admin_panel' => 'Доступ к панели управления',
    ],
];
