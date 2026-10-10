<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Akun development Admin dan Petugas.
     *
     * Aman dijalankan berulang kali: akun dicari berdasarkan email,
     * sehingga tidak ada duplikasi dan role selalu dikembalikan sesuai daftar ini.
     */
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Administrator',
                'email' => 'admin@smpd.test',
                'role' => 'admin',
            ],
            [
                'name' => 'Petugas Perpustakaan',
                'email' => 'petugas@smpd.test',
                'role' => 'petugas',
            ],
        ];

        foreach ($accounts as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make('password'),
                    'role' => $account['role'],
                ],
            );
        }
    }
}
