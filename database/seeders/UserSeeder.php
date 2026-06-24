<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([

            'name' => 'Srie',
            'email' => 'srie@mspublishing.com',
            'password' => bcrypt('password'),
            'role' => 'editor'

        ]);

        User::create([

            'name' => 'Eji',
            'email' => 'eji@mspublishing.com',
            'password' => bcrypt('password'),
            'role' => 'layouter'

        ]);

        User::create([

            'name' => 'Gumi',
            'email' => 'gumi@mspublishing.com',
            'password' => bcrypt('password'),
            'role' => 'designer'

        ]);
    }
}
