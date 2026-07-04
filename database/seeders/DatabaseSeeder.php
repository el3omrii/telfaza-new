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
        User::updateOrCreate(
            ['email' => 'el3omri@hotmail.fr'],
            [
                'name' => 'Amine Omri',
                'password' => bcrypt('123456'),
            ]
        );

        $this->call([
            CategorySeeder::class,
        ]);
    }
}
