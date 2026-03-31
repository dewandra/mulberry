<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        // Hanya buat super admin jika belum ada
        if (User::where('role', 'super_admin')->exists()) {
            $this->command->warn('⚠️  Super Admin sudah ada, skip.');
            return;
        }

        $user = User::create([
            'full_name' => env('ADMIN_NAME', 'Super Administrator'),
            'email'     => env('ADMIN_EMAIL', 'admin@act.com'),
            'password'  => Hash::make(env('ADMIN_PASSWORD', 'ChangeMeNow!2026')),
            'role'      => 'super_admin',
            'is_active' => true,
        ]);

        $this->command->info('✅ Super Admin berhasil dibuat.');
        $this->command->table(
            ['Name', 'Email', 'Password'],
            [[$user->full_name, $user->email, '(dari env ADMIN_PASSWORD)']]
        );
    }
}
