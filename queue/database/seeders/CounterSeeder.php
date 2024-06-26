<?php

namespace Database\Seeders;

use App\Models\Counter;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CounterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Counter::factory()->create([
            'name' => 'Counter 1',
        ]);

        Counter::factory()->create([
            'name' => 'Counter 2',
        ]);
    }
}
