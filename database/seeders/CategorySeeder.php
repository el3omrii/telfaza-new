<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'General',     'description' => null, 'color' => '#4A90E2'],
            ['name' => 'News',        'description' => null, 'color' => '#1E3A8A'],
            ['name' => 'Movies',      'description' => null, 'color' => '#8E44AD'],
            ['name' => 'Kids',        'description' => null, 'color' => '#FF9F43'],
            ['name' => 'Religious',   'description' => null, 'color' => '#D4AF37'],
            ['name' => 'Sports',      'description' => null, 'color' => '#E74C3C'],
            ['name' => 'Series',      'description' => null, 'color' => '#16A085'],
            ['name' => 'Music',       'description' => null, 'color' => '#E91E63'],
            ['name' => 'Documentary', 'description' => null, 'color' => '#27AE60'],
        ];

        foreach ($categories as $data) {
            $slug = Str::slug($data['name']);

            \App\Models\Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'color' => $data['color'],
                ]
            );
        }
    }
}
