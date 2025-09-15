<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Latest electronic devices and gadgets',
                'image' => null,
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'Clothing',
                'slug' => 'clothing',
                'description' => 'Fashionable clothing for all ages',
                'image' => null,
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'Home & Garden',
                'slug' => 'home-garden',
                'description' => 'Everything for your home and garden',
                'image' => null,
                'is_active' => true,
                'sort_order' => 3
            ],
            [
                'name' => 'Sports & Outdoors',
                'slug' => 'sports-outdoors',
                'description' => 'Sports equipment and outdoor gear',
                'image' => null,
                'is_active' => true,
                'sort_order' => 4
            ],
            [
                'name' => 'Books & Media',
                'slug' => 'books-media',
                'description' => 'Books, movies, and music',
                'image' => null,
                'is_active' => true,
                'sort_order' => 5
            ],
            [
                'name' => 'Health & Beauty',
                'slug' => 'health-beauty',
                'description' => 'Health products and beauty supplies',
                'image' => null,
                'is_active' => true,
                'sort_order' => 6
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
