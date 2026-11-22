<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = $this->seedAccount('admin@garasi.test', [
            'nik' => '3201010101010001',
            'name' => 'Admin Garasi',
            'phone' => '081200000001',
            'role' => 'admin',
            'status' => 'approved',
        ]);

        $this->seedAccount('pikapika@garasi.test', [
            'nik' => '3201020202020002',
            'name' => 'fijjyaaa',
            'phone' => '081300000002',
            'role' => 'penyewa',
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        $this->seedAccount('yeman@garasi.test', [
            'nik' => '3201030303030003',
            'name' => 'pikapika',
            'phone' => '081400000003',
            'role' => 'penyewa',
            'status' => 'pending',
        ]);

        $this->seedAccount('manusia@garasi.test', [
            'nik' => '3201040404040004',
            'name' => 'manusia',
            'phone' => '081500000004',
            'role' => 'penyewa',
            'status' => 'rejected',
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'rejection_notes' => 'Data KTP tidak sesuai.',
        ]);
    }

    /** @param array<string, mixed> $attributes */
    private function seedAccount(string $email, array $attributes): User
    {
        $user = User::firstOrCreate(['email' => $email], $attributes + ['password' => 'password']);
        $user->update(['name' => $attributes['name']]);

        return $user;
    }
}
