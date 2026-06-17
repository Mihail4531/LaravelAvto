<?php

namespace App\Filament\Pages\Auth;

use App\Support\Phone;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

/**
 * Личный кабинет сотрудника: имя, email, телефон и смена пароля.
 * Должность, филиал и роли здесь не редактируются — это зона раздела
 * «Сотрудники» (управляющий).
 */
class EditProfile extends BaseEditProfile
{
    public static function getLabel(): string
    {
        return 'Профиль';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('avatar_path')
                    ->label('Фотография')
                    ->image()
                    ->avatar()
                    ->imageEditor()
                    ->circleCropper()
                    ->disk('public')
                    ->directory('avatars')
                    ->maxSize(2048)
                    ->helperText('JPG или PNG, до 2 МБ.'),
                $this->getNameFormComponent()->label('Имя'),
                $this->getEmailFormComponent()->label('Email'),
                Phone::configure(TextInput::make('phone'))
                    ->label('Телефон')
                    ->maxLength(20),
                $this->getPasswordFormComponent()
                    ->label('Новый пароль')
                    ->helperText('Оставьте пустым, чтобы не менять.'),
                $this->getPasswordConfirmationFormComponent()->label('Подтверждение пароля'),
                $this->getCurrentPasswordFormComponent()->label('Текущий пароль'),
            ]);
    }
}
