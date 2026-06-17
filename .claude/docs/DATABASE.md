# Схема базы данных — АИС «Автосервис»

ER-диаграмма построена из миграций (`database/migrations`) со всеми полями.
Рендерится автоматически на GitHub и в любом Markdown-просмотрщике с поддержкой Mermaid
(VS Code: расширение *Markdown Preview Mermaid Support*).

> Экспорт в картинку для Word: открой файл в **VS Code** → Preview → ПКМ по диаграмме →
> *Copy Image*, либо вставь код на **mermaid.live** и выгрузи PNG/SVG.

```mermaid
erDiagram
    positions ||--o{ users : "должность"
    branches  ||--o{ users : "филиал"
    branches  ||--o{ time_slots : ""
    branches  ||--o{ appointments : ""
    branches  ||--o{ orders : ""
    car_brands ||--o{ car_models : ""
    car_brands ||--o{ cars : ""
    car_brands ||--o{ appointments : ""
    car_models ||--o{ cars : ""
    car_models ||--o{ appointments : ""
    car_models ||--o{ car_model_part : ""
    categories ||--o{ services : ""
    clients ||--o{ cars : ""
    clients ||--o{ orders : ""
    cars ||--o{ orders : ""
    time_slots ||--o| appointments : ""
    users ||--o{ appointments : "обработал"
    orders ||--o| appointments : ""
    appointments ||--o{ appointment_service : ""
    services ||--o{ appointment_service : ""
    parts ||--o{ car_model_part : ""
    parts ||--o{ part_movements : ""
    parts ||--o{ part_requests : ""
    parts ||--o{ order_part : ""
    orders ||--o{ part_movements : ""
    users ||--o{ part_movements : ""
    orders ||--o{ part_requests : ""
    users ||--o{ part_requests : "механик/выдал"
    orders ||--o{ order_service : ""
    services ||--o{ order_service : ""
    users ||--o{ order_service : "мастер"
    orders ||--o{ order_part : ""
    orders ||--o{ payments : ""
    users ||--o{ payments : "кассир"
    users ||--o{ orders : "приёмщик"
    roles ||--o{ model_has_roles : ""
    permissions ||--o{ model_has_permissions : ""
    permissions ||--o{ role_has_permissions : ""
    roles ||--o{ role_has_permissions : ""

    users {
        int id PK
        int position_id FK
        int branch_id FK
        varchar name
        varchar email
        timestamp email_verified_at
        varchar password
        varchar phone
        varchar avatar_path
        date hire_date
        boolean active
        varchar remember_token
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    positions {
        int id PK
        varchar name
        decimal hourly_rate
        varchar default_role
        timestamp created_at
        timestamp updated_at
    }
    branches {
        int id PK
        varchar name
        varchar slug
        varchar city
        varchar address
        varchar phone
        varchar email
        varchar work_hours
        varchar work_days_start
        varchar work_days_end
        time work_time_start
        time work_time_end
        decimal latitude
        decimal longitude
        boolean active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    car_brands {
        int id PK
        varchar name
        varchar slug
        varchar logo
        boolean active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    car_models {
        int id PK
        int car_brand_id FK
        varchar name
        varchar slug
        boolean active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    categories {
        int id PK
        varchar name
        varchar slug
        text description
        int sort_order
        boolean active
        timestamp created_at
        timestamp updated_at
    }
    services {
        int id PK
        int category_id FK
        varchar name
        varchar slug
        text description
        int duration_minutes
        decimal price
        varchar image
        boolean active
        int sort_order
        timestamp created_at
        timestamp updated_at
    }
    clients {
        int id PK
        varchar last_name
        varchar first_name
        varchar middle_name
        varchar phone
        varchar email
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    cars {
        int id PK
        int client_id FK
        int car_brand_id FK
        int car_model_id FK
        int year
        varchar vin
        varchar license_plate
        int mileage
        varchar color
        varchar fuel_type
        decimal engine_volume
        smallint power
        varchar transmission
        varchar body_type
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    time_slots {
        int id PK
        int branch_id FK
        datetime starts_at
        datetime ends_at
        boolean available
        timestamp created_at
        timestamp updated_at
    }
    appointments {
        int id PK
        int branch_id FK
        int time_slot_id FK
        int car_brand_id FK
        int car_model_id FK
        varchar client_name
        varchar client_phone
        varchar client_email
        text problem_description
        varchar status
        int processed_by FK
        datetime processed_at
        int order_id FK
        varchar reject_reason
        timestamp created_at
        timestamp updated_at
    }
    appointment_service {
        int id PK
        int appointment_id FK
        int service_id FK
    }
    parts {
        int id PK
        varchar article
        varchar name
        varchar unit
        decimal price
        int stock_quantity
        int reserved_quantity
        decimal min_stock_quantity
        varchar location
        boolean active
        boolean is_universal
        timestamp created_at
        timestamp updated_at
    }
    car_model_part {
        int id PK
        int part_id FK
        int car_model_id FK
        timestamp created_at
        timestamp updated_at
    }
    part_movements {
        int id PK
        int part_id FK
        int order_id FK
        int user_id FK
        varchar type
        decimal quantity
        varchar comment
        timestamp created_at
        timestamp updated_at
    }
    part_requests {
        int id PK
        int order_id FK
        int part_id FK
        int mechanic_id FK
        decimal quantity
        varchar status
        text comment
        int issued_by FK
        timestamp issued_at
        timestamp created_at
        timestamp updated_at
    }
    orders {
        int id PK
        int branch_id FK
        int client_id FK
        int car_id FK
        int receiver_id FK
        datetime planned_finish
        datetime actual_finish
        int current_mileage
        text damages_on_acceptance
        varchar equipment
        varchar fuel_level
        text problem_description
        varchar status
        decimal total_amount
        text comment
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    order_service {
        int id PK
        int order_id FK
        int service_id FK
        int executor_id FK
        decimal quantity
        decimal price
        decimal sum
        varchar status
        timestamp created_at
        timestamp updated_at
    }
    order_part {
        int id PK
        int order_id FK
        int part_id FK
        decimal quantity
        decimal price
        decimal sum
        boolean is_issued
        timestamp created_at
        timestamp updated_at
    }
    payments {
        int id PK
        int order_id FK
        int cashier_id FK
        datetime paid_at
        decimal amount
        varchar method
        varchar comment
        timestamp created_at
        timestamp updated_at
    }
    gallery_items {
        int id PK
        varchar title
        varchar caption
        varchar image
        varchar size
        int sort_order
        boolean active
        timestamp created_at
        timestamp updated_at
    }
    client_lookup_codes {
        int id PK
        varchar email
        varchar code
        smallint attempts
        timestamp expires_at
        timestamp used_at
        varchar ip_address
        timestamp created_at
        timestamp updated_at
    }
    notifications {
        uuid id PK
        varchar type
        varchar notifiable_type
        int notifiable_id
        text data
        timestamp read_at
        timestamp created_at
        timestamp updated_at
    }
    roles {
        int id PK
        varchar name
        varchar guard_name
        timestamp created_at
        timestamp updated_at
    }
    permissions {
        int id PK
        varchar name
        varchar guard_name
        timestamp created_at
        timestamp updated_at
    }
    model_has_roles {
        int role_id FK
        varchar model_type
        int model_id
    }
    model_has_permissions {
        int permission_id FK
        varchar model_type
        int model_id
    }
    role_has_permissions {
        int permission_id FK
        int role_id FK
    }
```
