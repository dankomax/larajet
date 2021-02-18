<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use \App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory()->count(3)->create();
        // \App\Models\Item::factory()->count(10)->create();
        User::factory()
            ->count(3)
            ->hasItems(30)
            ->create();
    }
}
