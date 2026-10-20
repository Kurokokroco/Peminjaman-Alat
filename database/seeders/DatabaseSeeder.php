<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'nama_lengkap' => 'administrator',
            'username' => 'admin',
            'password' => 'admin123',
            'role' => 'admin',
        ]);
        User::create([
            'nama_lengkap' => 'Petugas Gudang',
            'username' => 'petugas',
            'password' => 'petugas123',
            'role' => 'petugas',    
        ]);
        User::create([
            'nama_lengkap' => 'Budi',
            'username' => 'budi',
            'password' => 'budi123',
            'role' => 'peminjam',
        ]); 
    }
}
