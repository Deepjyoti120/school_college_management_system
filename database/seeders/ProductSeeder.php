<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::insert([
            [
                'id' => Str::ulid()->toBase32(),
                'name' => 'PPC Cement (50kg)',
                'description' => 'High-quality Portland cement ideal for general construction.',
                'price' => 450.00,
                'stock' => 0,
                'sku' => 'CEM-PPC-50KG',
                'image_url' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::ulid()->toBase32(),
                'name' => 'Dhalai Special Cement (50kg)',
                'description' => 'Premium grade cement suitable for RCC, plastering, and brickwork.',
                'price' => 500.00,
                'stock' => 0,
                'sku' => 'CEM-DHALAI-SPECIAL-50KG',
                'image_url' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
