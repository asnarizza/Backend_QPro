<?php

namespace Database\Seeders;

use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(DepartmentSeeder::class);
        $this->call(CounterSeeder::class);

        User::factory()->create([
            'name' => 'wafir',
            //'role_id' => '',
            'phone' => '01161636065',
            'email' => 'wafir@gmail.com',
            'password' => 'wafir1234'
        ]);

        User::factory()->create([
            'name' => 'rose',
            'role_id' => '1',
            'phone' => '0194096544',
            'email' => 'rose@gmail.com',
            'password' => 'admin1234'
        ]);

        User::factory()->create([
            'name' => 'adliyana',
            'role_id' => '2',
            'phone' => '0177923193',
            'email' => 'yana@gmail.com',
            'password' => 'staff1234'
        ]);

    }
}
