<?php

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;

test('the first system administrator is created from the environment and must change the password (OQ-040)', function () {
    config()->set('app.system_administrator', [
        'name' => 'Administración',
        'email' => 'admin@ejemplo.test',
        'password' => 'clave-de-prueba',
    ]);

    $this->seed(AdminUserSeeder::class);

    $administrator = User::query()->where('email', 'admin@ejemplo.test')->sole();

    expect($administrator->must_change_password)->toBeTrue()
        ->and($administrator->is_active)->toBeTrue()
        ->and($administrator->hasRole(RoleName::SystemAdministrator))->toBeTrue();
});

test('the seeder refuses to run without the administrator email and password', function () {
    config()->set('app.system_administrator.email', null);

    $this->seed(AdminUserSeeder::class);
})->throws(RuntimeException::class);
