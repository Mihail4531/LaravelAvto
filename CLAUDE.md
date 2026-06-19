# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

АИС «Автосервис» — a car-service (auto repair shop) management system built on **Laravel 12** with a **Filament 5** admin panel. The entire UI is in **Russian**; keep labels, notifications, and comments in Russian to match existing code. PHP 8.2+, SQLite by default, Tailwind 4 + Vite.

## Documentation

Project docs by concern — **read the relevant one before working in that area**:
- `.claude/docs/DESIGN.md` — UI/design: brand tokens (steel+cobalt), public-site classes, Filament theming + login class map.
- `.claude/docs/PRODUCT.md` — product/business: users, purpose, brand strategy, design principles.
- `README.md` — setup/about.

## Commands

```bash
composer dev          # Run server + queue listener + vite concurrently (main dev command)
composer test         # config:clear, then artisan test
composer setup        # Full first-time setup (install, key, migrate, npm build)

php artisan test                              # Run all tests (PHPUnit)
php artisan test --filter=SomeTest            # Run a single test class/method
php artisan migrate                           # Apply migrations
php artisan db:seed --class=RolesAndPermissionsSeeder   # Seed roles/permissions (NOT in DatabaseSeeder)
php artisan db:seed --class=PositionsSeeder            # Seed job positions (NOT in DatabaseSeeder)

vendor/bin/pint       # Format/lint PHP (Laravel Pint) — run before committing
npm run dev           # Vite dev server only
npm run build         # Production asset build
```

Note: `php artisan db:seed` (DatabaseSeeder) only runs `CatalogSeeder`. `RolesAndPermissionsSeeder` and `PositionsSeeder` must be run explicitly (in that order — positions reference role names).

## Architecture

### Two surfaces
- **Filament admin panel** at `/admin` — the staff-facing CRUD app. Single panel defined in `app/Providers/Filament/AdminPanelProvider.php`; Resources and Pages are **auto-discovered** from `app/Filament/Resources` and `app/Filament/Pages` (don't manually register them).
- **Public site** (`routes/web.php`) — marketing home (`HomeController`), a Livewire **`BookingWizard`** (`/booking`) for customers to book appointments, and a passwordless **client lookup** area (`/my-visits`) gated by an email **OTP code** (`ClientLookupController` + `ClientLookupCode`, rate-limited via `throttle` middleware). Staff-only HTML reports live under `/reports` (`ReportsController`).

### Authorization (Spatie + Filament)
Permission-driven, not role-driven, in app code. Key pieces:
- `app/Filament/Traits/ResourcePermissions.php` — applied to every Filament Resource. It derives the permission key from the model name (`App\Models\CarBrand` → `car_brand`) and checks `view_any_<key>`, `view_<key>`, `create_<key>`, `update_<key>`, `delete_<key>`. **When adding a Resource, `use \App\Filament\Traits\ResourcePermissions;` and add matching permissions to `RolesAndPermissionsSeeder::RESOURCES`.**
- `super_admin` bypasses all checks via `Gate::before` in `AppServiceProvider`. This is the **technical break-glass / install account**, not a business role: it is the only account immune to permission misconfiguration (so it can always recover a locked-out shop) and the only one that may edit/delete the `super_admin` role itself (`RoleResource::isEditableRole`) and access the raw permission catalog (`PermissionResource`). In a sold installation it is held by the owner/integrator and not handed out as a staff position.
- `director` (Управляющий) is the **top business account**: it holds *all* permissions, **including role management** (`view_any_role`/`create_role`/`update_role`/`delete_role`), so each shop configures its own staff and roles from the admin without the vendor. The only things it lacks vs `super_admin` are the `Gate::before` bypass and editing the `super_admin` role / raw permission catalog. The `Roles` section (`AccessControl\RoleResource`) is gated by the `role` permissions (NOT hardcoded to `super_admin`); `Permissions` (`PermissionResource`) stays `super_admin`-only (it's a code-level catalog).
- **Self-lockout guard**: when a user edits a role they currently hold, `RoleResource` shows a live red warning if they uncheck `access_admin_panel`, and `EditRole` requires a confirmation modal on save (`getSaveFormAction` + `editingOwnRole`). Prevents the director from accidentally removing their own access.
- Roles, permissions, and the default admin (`admin@mail.ru` / `password`) are defined in `database/seeders/RolesAndPermissionsSeeder.php`. Roles: `super_admin`, `director`, `receptionist`, `foreman` (старший мастер), `warehouseman`, `mechanic`. Per the ТЗ the `foreman` (старший мастер) coordinates the shop: it owns `assign_order_executor` (assigning a mechanic to a service) and `change_order_status` (approving/closing work orders) — the `receptionist` forms and hands off the order but does NOT assign executors or change order status. Custom (non-CRUD) permissions like `issue_part`, `change_order_status`, `access_admin_panel` are listed in `CUSTOM_PERMISSIONS`.
- **Role assignment is automatic**: a user's Spatie role is synced from their `Position.default_role` by `UserObserver`. Don't assign business roles manually; set the user's position. Technical roles not tied to a position (e.g. `super_admin`) are preserved across syncs.
- Panel access (`User::canAccessPanel`) requires `active = true` AND (`super_admin` OR `access_admin_panel`).
- Human-readable role/permission names come from `lang/ru/roles.php` and `lang/ru/permissions.php` via `App\Support\AccessLabels`.
- **Branch scoping (multi-branch)** — `App\Support\BranchScope`. Business model is **one company with several locations**, so this is a *staff work-area*, not tenant isolation. `view_all_branches` (a `CUSTOM_PERMISSIONS` perm, held by `director` + `super_admin`) sees the whole network; everyone else sees only **their own branch** (`User.branch_id`). Apply it in a resource's `getEloquentQuery` via `BranchScope::apply($query)` (direct `branch_id` column — Orders, Appointments, TimeSlots) or `BranchScope::applyViaRelation($query, 'order')` (indirect — PartRequests via the order's branch). Forms prefill + lock the branch field for scoped users (`BranchScope::defaultBranchId()` + `->disabled(BranchScope::isRestricted())`); branch table columns/filters show only when `BranchScope::shouldShowBranchUi()` (= sees-all **and** more than one branch), so the UI stays clean while a single branch exists and lights up automatically with the second. A staff member with `branch_id = null` is **unrestricted** (floating until assigned). Intentionally **not** scoped (shared across the network): catalog/reference data, clients & their cars (a client may visit any location), and the `Users` HR resource. `mechanic` is additionally narrowed to their own services/requests as before.

### Filament Resource layout
Each resource is a folder under `app/Filament/Resources/<Name>/`:
- `<Name>Resource.php` — config, navigation, `getEloquentQuery`, relations, pages
- `Schemas/<Name>Form.php` — form schema (`::configure($schema)`)
- `Tables/<Name>Table.php` — table schema (`::configure($table)`)
- `RelationManagers/` and `Pages/` as needed

`OrderResource::getEloquentQuery` is the example of row-level scoping: a `mechanic` (without `director`/`super_admin`) only sees orders containing services where they are the `executor_id`.

### Domain / business logic (in models & observers)
- **Order** (`app/Models/Order.php`): statuses via constants + `statuses()`; `booted()` releases part reserves and logs a `PartMovement` when an order is cancelled; `recalculateTotal()` sums service + part pivots (uses `saveQuietly`); `paid_amount` / `remaining_amount` accessors aggregate `Payment`s. Orders use `SoftDeletes`.
- **Part** (`app/Models/Part.php`): `available_quantity = stock_quantity - reserved_quantity`; `isLowStock()`; stock changes are audited through `PartMovement` (with `type` constants like `TYPE_RELEASE`).
- **Observers** (registered in `AppServiceProvider::boot`):
  - `UserObserver` — role/position sync (above). `User` uses `SoftDeletes`: employees are referenced by historical FKs (`orders.receiver_id`, `order_service.executor_id`, `payments.cashier_id`, all RESTRICT), so deleting is soft (`deleted_at`) — never force-delete an employee with linked records.
  - `PartObserver` — sends a Filament database notification to users with `receive_part` only when available stock **crosses** below `min_stock_quantity` (avoids repeat spam).
  - `AppointmentObserver` — notifications for new appointments.
  - `PartRequestObserver` — no-op (kept registered). Parts are now self-issued on creation, so there is no "pending request" to notify about; low-stock alerts still come from `PartObserver` when stock is decremented.
- **Car specs live on `Car`, not `CarModel`**: fuel_type, engine_volume, power, transmission, body_type belong to the client's specific vehicle (`Car`, with `Car::fuelTypes()/transmissions()/bodyTypes()` option maps). `CarModel` is a plain reference (brand → model name) to avoid a model-variant explosion.
- **Part compatibility (применяемость)**: `Part.is_universal` (fits any car) + a `car_model_part` pivot (`Part::carModels()`) listing models a part fits. `Part::fitsModel($carModelId)` returns true for universal parts, parts with no compatibility data (soft mode — don't nag), or a matching model. When adding a part to an order, a non-blocking warning shows if the part isn't marked compatible with the order's car model.
- **Parts workflow (self-issue)**: any role that works with orders (`mechanic`, `foreman`, `receptionist`, `warehouseman`, `director`, `super_admin` — those with `create_part_request`) issues a part **immediately**, without a warehouseman confirmation step. Creating a `PartRequest` (resource `PartRequests`, labelled **«Выдача запчастей»**) auto-runs `PartRequest::fulfill()` in `CreatePartRequest::handleRecordCreation` — decrements stock, attaches the part to the order as issued, logs a `PartMovement`, recalculates the order total, and sets status `issued` + `issued_by`/`issued_at` = current user. The `PartRequests` list is therefore an **issue log** (who/when/which part/qty/order). You cannot issue more than `available_quantity` (hard check in `fulfill()` → surfaced as a validation error on the quantity field). `warehouseman` is no longer a gate for issuing — it remains the role for **receiving** stock (`receive_part`). `issue_part` permission is retained but no longer required to take parts. Mechanics still only see their own issues (`getEloquentQuery`) and their own orders.
  - **Single issue path**: there is **no reserve stage** anymore. Adding a part from inside an order — both the order's **Parts tab** (`PartsRelationManager` → header action «Добавить запчасть») and the order **View** page action «Выдать запчасть» — runs the same `PartRequest::create()` + `fulfill()`, so every taken part is immediately issued *and* appears in the «Выдача запчастей» journal. Editing a line's qty adjusts stock by the delta (`PartMovement`); deleting an issued line returns it to stock (`TYPE_ISSUE_UNDO`). The legacy reserve branch is kept only to drain pre-existing `is_issued = false` rows.

Order ↔ Service and Order ↔ Part are many-to-many with rich pivots (`order_service`: `executor_id, quantity, price, sum, status`; `order_part`: `quantity, price, sum, is_issued`).

### Conventions
- Reusable helpers live in `app/Support/` (e.g. `ExcelExporter`, `AccessLabels`) and `app/Filament/Support/`.
- Notifications use Filament's database notifications (polled every 30s; enabled in the panel provider).
- Locale is `ru`; `Carbon::setLocale('ru')` is set in `AppServiceProvider`.
