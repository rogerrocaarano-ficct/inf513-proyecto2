<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * The first system administrator account, from the environment file (OQ-040).
 */
class AdminUserSeeder extends Seeder
{
    /**
     * Create the administrator once; an existing account is left as it is.
     */
    public function run(): void
    {
        $email = config('app.system_administrator.email');
        $password = config('app.system_administrator.password');

        if (blank($email) || blank($password)) {
            throw new RuntimeException('Defina ADMIN_EMAIL y ADMIN_PASSWORD en el archivo .env antes de sembrar.');
        }

        $administrator = User::query()->firstOrCreate(['email' => $email], [
            'name' => config('app.system_administrator.name'),
            'password' => $password,
            'email_verified_at' => now(),
            'is_active' => true,
            // UC-018 BR-005: The user changes the initial password on the first login.
            'must_change_password' => true,
        ]);

        $administrator->roles()->syncWithoutDetaching(
            Role::query()->where('name', RoleName::SystemAdministrator)->pluck('id'),
        );
    }
}
