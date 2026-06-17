<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;
use SensitiveParameter;

/**
 * Вход в АИС по логину сотрудника, а не по email.
 * Логин — короткий и удобный идентификатор для персонала; email остаётся
 * для уведомлений и восстановления доступа, но в форму входа не выводится.
 */
class Login extends BaseLogin
{
    /**
     * В форме входа — поле «Логин» вместо email.
     */
    protected function getLoginFormComponent(): Component
    {
        return TextInput::make('login')
            ->label('Логин')
            ->required()
            ->autocomplete('username')
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getLoginFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    /**
     * Авторизуемся по полю login, а не email.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        return [
            'login' => $data['login'],
            'password' => $data['password'],
        ];
    }

    /**
     * Единое понятное сообщение при неверных данных — без подсказки,
     * что именно неверно (логин или пароль).
     */
    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.login' => 'Неверный логин или пароль.',
        ]);
    }
}
