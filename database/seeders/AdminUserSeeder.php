<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear superadmin por defecto
        AdminUser::updateOrCreate(
            ['email' => 'admin@lynkamed.com'],
            [
                'name' => 'Admin Lynkamed',
                'password' => Hash::make('Lynkamed2024!'), // CAMBIAR EN PRODUCCIÓN
                'role' => 'superadmin',
                'activo' => true,
            ]
        );

        $this->command->info('Admin user created: admin@lynkamed.com');
        $this->command->warn('Default password: Lynkamed2024! - CHANGE THIS IN PRODUCTION!');
    }
}
