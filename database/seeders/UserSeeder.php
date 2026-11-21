<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // --- Admin ---
        User::create([
            'nik'      => '3201010101010001',
            'name'     => 'Admin Garasi',
            'email'    => 'admin@garasi.test',
            'phone'    => '081200000001',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'status'   => 'approved',
        ]);

        // --- Penyewa 1: sudah APPROVED ---
        $penyewa1 = User::create([
            'nik'      => '3201020202020002',
            'name'     => 'Budi Santoso',
            'email'    => 'budi@garasi.test',
            'phone'    => '081300000002',
            'password' => Hash::make('password'),
            'role'     => 'penyewa',
            'status'   => 'approved',
        ]);

        // Set approved_by = admin (self-approve via seeder)
        $admin = User::where('email', 'admin@garasi.test')->first();
        $penyewa1->update([
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // --- Penyewa 2: masih PENDING ---
        User::create([
            'nik'      => '3201030303030003',
            'name'     => 'Siti Rahayu',
            'email'    => 'siti@garasi.test',
            'phone'    => '081400000003',
            'password' => Hash::make('password'),
            'role'     => 'penyewa',
            'status'   => 'pending',
        ]);

        // --- Penyewa 3: REJECTED ---
        User::create([
            'nik'      => '3201040404040004',
            'name'     => 'Andi Wijaya',
            'email'    => 'andi@garasi.test',
            'phone'    => '081500000004',
            'password' => Hash::make('password'),
            'role'     => 'penyewa',
            'status'   => 'rejected',
            'approved_by'     => $admin->id,
            'approved_at'     => now(),
            'rejection_notes' => 'Data KTP tidak sesuai.',
        ]);
    }
}
