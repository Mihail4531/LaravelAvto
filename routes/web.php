<?php

use Illuminate\Support\Facades\Route;

Route::get('/assign-role', function () {
    $user = \App\Models\User::where('email', 'admin@mail.ru')->first();
    if (!$user) {
        $user = \App\Models\User::create([
            'name' => 'Admin',
            'email' => 'user@mail.ru',
            'password' => bcrypt('password'),
            'active' => true,
        ]);
    }
    $user->assignRole('admin');
    return 'Роль admin назначена пользователю ' . $user->email;
});
Route::get('/users', function () {
    $users = \App\Models\User::all(['id', 'name', 'email']);
    return response()->json($users);
});
