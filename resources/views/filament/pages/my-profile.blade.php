<x-filament-panels::page>
    @php
        /** @var \App\Models\User $user */
        $user = $this->getUser();

        $avatar = $user->getFilamentAvatarUrl();

        $initials = collect(explode(' ', trim($user->name)))
            ->filter()
            ->take(2)
            ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
            ->implode('');

        $roleNames = $user->roles
            ->pluck('name')
            ->map(fn ($r) => \App\Support\AccessLabels::role($r))
            ->all();

        $fields = array_filter([
            ['heroicon-o-envelope',        'Email',     $user->email],
            ['heroicon-o-phone',           'Телефон',   $user->phone],
            ['heroicon-o-briefcase',       'Должность', $user->position?->name],
            ['heroicon-o-building-office',  'Филиал',    $user->branch?->name],
            ['heroicon-o-calendar-days',   'Принят',    $user->hire_date?->format('d.m.Y')],
        ], fn ($row) => filled($row[2]));
    @endphp

    <style>
        .profile-hero {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1.75rem;
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(99,102,241,0.12), rgba(14,165,233,0.10));
            border: 1px solid rgba(99,102,241,0.18);
        }
        .dark .profile-hero {
            background: linear-gradient(135deg, rgba(99,102,241,0.22), rgba(14,165,233,0.14));
            border-color: rgba(129,140,248,0.25);
        }
        .profile-avatar, .profile-avatar-fallback {
            width: 96px;
            height: 96px;
            border-radius: 9999px;
            object-fit: cover;
            flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(0,0,0,0.12);
            border: 3px solid #fff;
        }
        .dark .profile-avatar, .dark .profile-avatar-fallback {
            border-color: rgba(255,255,255,0.12);
        }
        .profile-avatar-fallback {
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #6366f1, #0ea5e9);
            color: #fff;
            font-size: 34px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        .profile-name {
            font-size: 24px;
            font-weight: 700;
            color: rgb(17 24 39);
            line-height: 1.2;
        }
        .dark .profile-name { color: #fff; }
        .profile-sub {
            font-size: 14px;
            color: rgb(75 85 99);
            margin-top: 2px;
        }
        .dark .profile-sub { color: rgb(156 163 175); }
        .profile-badges { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
        .profile-badge {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: rgb(67 56 202);
            background: rgba(99,102,241,0.12);
            padding: 3px 9px;
            border-radius: 9999px;
        }
        .dark .profile-badge { color: rgb(199 210 254); background: rgba(99,102,241,0.22); }
        .profile-status {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 9999px;
        }
        .profile-status.is-active { color: rgb(4 120 87); background: rgba(16,185,129,0.14); }
        .profile-status.is-inactive { color: rgb(159 18 57); background: rgba(244,63,94,0.14); }
        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
        }
        .profile-field { display: flex; align-items: center; gap: 12px; }
        .profile-field-icon {
            width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            background: rgba(99,102,241,0.10);
        }
        .dark .profile-field-icon { background: rgba(99,102,241,0.20); }
        .profile-field-label {
            font-size: 11px; font-weight: 600; text-transform: uppercase;
            letter-spacing: 0.06em; color: rgb(107 114 128);
        }
        .dark .profile-field-label { color: rgb(156 163 175); }
        .profile-field-value { font-size: 15px; font-weight: 600; color: rgb(17 24 39); }
        .dark .profile-field-value { color: #fff; }
    </style>

    {{-- ═════ Шапка профиля ═════ --}}
    <div class="profile-hero">
        @if ($avatar)
            <img src="{{ $avatar }}" alt="{{ $user->name }}" class="profile-avatar">
        @else
            <div class="profile-avatar-fallback">{{ $initials ?: '—' }}</div>
        @endif

        <div style="flex:1; min-width:0;">
            <div class="profile-name">{{ $user->name }}</div>
            <div class="profile-sub">{{ $user->position?->name ?? 'Должность не указана' }}</div>

            <div class="profile-badges">
                @forelse ($roleNames as $role)
                    <span class="profile-badge">{{ $role }}</span>
                @empty
                    <span class="profile-badge">Без роли</span>
                @endforelse

                <span class="profile-status {{ $user->active ? 'is-active' : 'is-inactive' }}">
                    {{ $user->active ? 'Активен' : 'Неактивен' }}
                </span>
            </div>
        </div>
    </div>

    {{-- ═════ Контактные и служебные данные ═════ --}}
    <x-filament::section>
        <x-slot name="heading">Данные сотрудника</x-slot>

        <div class="profile-grid">
            @foreach ($fields as [$icon, $label, $value])
                <div class="profile-field">
                    <div class="profile-field-icon">
                        <x-filament::icon :icon="$icon" style="width:20px; height:20px; color:rgb(99 102 241);"/>
                    </div>
                    <div>
                        <div class="profile-field-label">{{ $label }}</div>
                        <div class="profile-field-value">{{ $value }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-panels::page>
