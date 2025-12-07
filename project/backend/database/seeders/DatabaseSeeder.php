<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. INSÉRER D'ABORD LES USERS
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@blog.com',
                'password' => Hash::make('Admin123!'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'John Doe',
                'email' => 'john@blog.com',
                'password' => Hash::make('Password123'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@blog.com',
                'password' => Hash::make('MySecret456'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('users')->insert($users);

        // 2. ENSUITE APPELER LE SEEDER DES ARTICLES
        $this->call(ArticlesSeeder::class);

        // 3. ENSUITE LES COMMENTAIRES
        $this->call(CommentsSeeder::class);
    }
}
