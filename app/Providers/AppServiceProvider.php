<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Part;
use App\Models\PartRequest;
use App\Models\User;
use App\Observers\AppointmentObserver;
use App\Observers\PartObserver;
use App\Observers\PartRequestObserver;
use App\Observers\UserObserver;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Carbon::setLocale('ru');

        // Супер-админ получает все права автоматически, без явного назначения.
        Gate::before(function ($user) {
            return $user instanceof User && $user->hasRole('super_admin') ? true : null;
        });

        // Синхронизация Spatie-роли пользователя с его должностью.
        User::observe(UserObserver::class);

        // Уведомления: новая заявка и низкий остаток на складе.
        Appointment::observe(AppointmentObserver::class);
        Part::observe(PartObserver::class);
        PartRequest::observe(PartRequestObserver::class);
    }
}
