<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Super Admin',
            'Admin',
            'Marketing',
            'Quality Control',
            'Packaging',
            'Pewarna Alam',
            'Sosial Media',
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role],    // unique check
                ['name' => $role]
            );
        }
    }
}
