<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $electronicsCategory = Category::where('slug', 'electronics')->first();
        $clothingCategory = Category::where('slug', 'clothing')->first();
        $homeCategory = Category::where('slug', 'home-garden')->first();

        // Electronics Products
        Product::create([
            'name' => 'Smartphone X1',
            'slug' => 'smartphone-x1',
            'description' => 'Latest smartphone with advanced features and high-quality camera',
            'price' => 599.99,
            'sale_price' => 499.99,
            'stock_quantity' => 50,
            'sku' => 'ELEC-SMART-X1',
            'images' => ['/images/products/smartphone-1.jpg', '/images/products/smartphone-2.jpg'],
            'specifications' => [
                'screen' => '6.1 inch OLED',
                'storage' => '128GB',
                'ram' => '8GB',
                'camera' => '48MP + 12MP + 12MP'
            ],
            'is_active' => true,
            'is_featured' => true,
            'category_id' => $electronicsCategory->id
        ]);

        Product::create([
            'name' => 'Laptop Pro',
            'slug' => 'laptop-pro',
            'description' => 'Professional laptop for work and gaming',
            'price' => 1299.99,
            'stock_quantity' => 25,
            'sku' => 'ELEC-LAPTOP-PRO',
            'images' => ['/images/products/laptop-1.jpg'],
            'specifications' => [
                'processor' => 'Intel i7-12700H',
                'ram' => '16GB DDR4',
                'storage' => '512GB SSD',
                'graphics' => 'RTX 3060'
            ],
            'is_active' => true,
            'is_featured' => true,
            'category_id' => $electronicsCategory->id
        ]);

        // Clothing Products
        Product::create([
            'name' => 'Classic T-Shirt',
            'slug' => 'classic-t-shirt',
            'description' => 'Comfortable cotton t-shirt in various colors',
            'price' => 24.99,
            'stock_quantity' => 100,
            'sku' => 'CLOTH-TSHIRT-001',
            'images' => ['/images/products/tshirt-1.jpg'],
            'specifications' => [
                'material' => '100% Cotton',
                'fit' => 'Regular',
                'sizes' => 'S, M, L, XL'
            ],
            'is_active' => true,
            'is_featured' => false,
            'category_id' => $clothingCategory->id
        ]);

        Product::create([
            'name' => 'Denim Jeans',
            'slug' => 'denim-jeans',
            'description' => 'High-quality denim jeans with perfect fit',
            'price' => 79.99,
            'sale_price' => 59.99,
            'stock_quantity' => 75,
            'sku' => 'CLOTH-JEANS-001',
            'images' => ['/images/products/jeans-1.jpg'],
            'specifications' => [
                'material' => 'Denim',
                'fit' => 'Slim',
                'sizes' => '30x32, 32x32, 34x32, 36x32'
            ],
            'is_active' => true,
            'is_featured' => true,
            'category_id' => $clothingCategory->id
        ]);

        // Home & Garden Products
        Product::create([
            'name' => 'Coffee Maker',
            'slug' => 'coffee-maker',
            'description' => 'Automatic coffee maker with timer and multiple settings',
            'price' => 89.99,
            'stock_quantity' => 30,
            'sku' => 'HOME-COFFEE-001',
            'images' => ['/images/products/coffee-maker-1.jpg'],
            'specifications' => [
                'capacity' => '12 cups',
                'features' => 'Auto-shutoff, Programmable',
                'color' => 'Black'
            ],
            'is_active' => true,
            'is_featured' => false,
            'category_id' => $homeCategory->id
        ]);

        Product::create([
            'name' => 'Garden Tool Set',
            'slug' => 'garden-tool-set',
            'description' => 'Complete set of essential garden tools',
            'price' => 149.99,
            'sale_price' => 119.99,
            'stock_quantity' => 20,
            'sku' => 'HOME-GARDEN-001',
            'images' => ['/images/products/garden-tools-1.jpg'],
            'specifications' => [
                'pieces' => '8 tools',
                'material' => 'Stainless Steel',
                'storage' => 'Included case'
            ],
            'is_active' => true,
            'is_featured' => true,
            'category_id' => $homeCategory->id
        ]);
    }
}
