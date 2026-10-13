<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PeranSeeder extends Seeder
{
    /**
     * Jalankan seeder peran pengguna.
     */
    public function run(): void
    {
        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value]);
        }
    }
}
