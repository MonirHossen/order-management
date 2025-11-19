<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        
        $electronics = Category::firstOrCreate(
            ['slug' => 'electronics'],
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and gadgets',
                'is_active' => true,
            ]
        );

        $clothing = Category::firstOrCreate(
            ['slug' => 'clothing'],
            [
                'name' => 'Clothing',
                'description' => 'Apparel and fashion',
                'is_active' => true,
            ]
        );

        $books = Category::firstOrCreate(
            ['slug' => 'books'],
            [
                'name' => 'Books',
                'description' => 'Books and publications',
                'is_active' => true,
            ]
        );


        // Get vendors
        $vendor1 = User::role('vendor')->first();
        $vendor2 = User::role('vendor')->skip(1)->first();

        // Create Electronics Products
        $laptop = Product::create([
            'name' => 'MacBook Pro 16"',
            'slug' => 'macbook-pro-16',
            'sku' => 'ELEC-001',
            'description' => 'Powerful laptop for professionals',
            'short_description' => 'High-performance laptop',
            'price' => 2499.99,
            'compare_price' => 2799.99,
            'cost_price' => 2000.00,
            'category_id' => $electronics->id,
            'vendor_id' => $vendor1?->id,
            'brand' => 'Apple',
            'is_active' => true,
            'is_featured' => true,
            'stock_quantity' => 50,
            'low_stock_threshold' => 10,
            'images' => ['https://example.com/laptop1.jpg', 'https://example.com/laptop2.jpg'],
        ]);

        Product::create([
            'name' => 'iPhone 15 Pro',
            'slug' => 'iphone-15-pro',
            'sku' => 'ELEC-002',
            'description' => 'Latest iPhone with advanced features',
            'short_description' => 'Premium smartphone',
            'price' => 999.99,
            'compare_price' => 1099.99,
            'cost_price' => 800.00,
            'category_id' => $electronics->id,
            'vendor_id' => $vendor1?->id,
            'brand' => 'Apple',
            'is_active' => true,
            'is_featured' => true,
            'stock_quantity' => 100,
            'low_stock_threshold' => 20,
        ]);

        // Create Clothing Products with Variants
        $tshirt = Product::create([
            'name' => 'Premium Cotton T-Shirt',
            'slug' => 'premium-cotton-tshirt',
            'sku' => 'CLOTH-001',
            'description' => 'Comfortable cotton t-shirt for everyday wear',
            'short_description' => 'Soft cotton tee',
            'price' => 29.99,
            'compare_price' => 39.99,
            'cost_price' => 15.00,
            'category_id' => $clothing->id,
            'vendor_id' => $vendor2?->id,
            'brand' => 'Urban Style',
            'is_active' => true,
            'is_featured' => false,
            'stock_quantity' => 0, // Managed by variants
            'low_stock_threshold' => 10,
        ]);

        // Add variants to t-shirt
        $tshirt->variants()->createMany([
            [
                'sku' => 'CLOTH-001-S-BLK',
                'name' => 'Small - Black',
                'price' => 29.99,
                'stock_quantity' => 25,
                'attributes' => ['size' => 'S', 'color' => 'Black'],
                'is_active' => true,
            ],
            [
                'sku' => 'CLOTH-001-M-BLK',
                'name' => 'Medium - Black',
                'price' => 29.99,
                'stock_quantity' => 30,
                'attributes' => ['size' => 'M', 'color' => 'Black'],
                'is_active' => true,
            ],
            [
                'sku' => 'CLOTH-001-L-BLK',
                'name' => 'Large - Black',
                'price' => 29.99,
                'stock_quantity' => 20,
                'attributes' => ['size' => 'L', 'color' => 'Black'],
                'is_active' => true,
            ],
            [
                'sku' => 'CLOTH-001-S-WHT',
                'name' => 'Small - White',
                'price' => 29.99,
                'stock_quantity' => 5, // Low stock
                'attributes' => ['size' => 'S', 'color' => 'White'],
                'is_active' => true,
            ],
        ]);

        // Create Books
        Product::create([
            'name' => 'Clean Code',
            'slug' => 'clean-code',
            'sku' => 'BOOK-001',
            'description' => 'A Handbook of Agile Software Craftsmanship',
            'short_description' => 'Programming best practices',
            'price' => 44.99,
            'compare_price' => 54.99,
            'cost_price' => 30.00,
            'category_id' => $books->id,
            'vendor_id' => $vendor2?->id,
            'brand' => 'Prentice Hall',
            'is_active' => true,
            'is_featured' => true,
            'stock_quantity' => 75,
            'low_stock_threshold' => 15,
        ]);

        // Create low stock product
        Product::create([
            'name' => 'Wireless Mouse',
            'slug' => 'wireless-mouse',
            'sku' => 'ELEC-003',
            'description' => 'Ergonomic wireless mouse',
            'short_description' => 'Comfortable mouse',
            'price' => 24.99,
            'cost_price' => 12.00,
            'category_id' => $electronics->id,
            'vendor_id' => $vendor1?->id,
            'brand' => 'Logitech',
            'is_active' => true,
            'is_featured' => false,
            'stock_quantity' => 5, // Low stock
            'low_stock_threshold' => 10,
        ]);

        // Create out of stock product
        Product::create([
            'name' => 'Gaming Keyboard',
            'slug' => 'gaming-keyboard',
            'sku' => 'ELEC-004',
            'description' => 'Mechanical gaming keyboard with RGB',
            'short_description' => 'RGB gaming keyboard',
            'price' => 89.99,
            'cost_price' => 50.00,
            'category_id' => $electronics->id,
            'vendor_id' => $vendor1?->id,
            'brand' => 'Razer',
            'is_active' => true,
            'is_featured' => false,
            'stock_quantity' => 0, // Out of stock
            'low_stock_threshold' => 5,
        ]);
    }
}