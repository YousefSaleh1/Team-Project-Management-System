<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StroeAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'     => "Admin",
            'email'    => 'yousef@admin.com',
            'password' => 'admin1234',
            'is_admin' => true
        ]);
    }
}
