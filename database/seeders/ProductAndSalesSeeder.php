<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductAndSalesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ==========================
        // PRODUCTS
        // ==========================
        $products = [
            [
                'name' => 'Laptop Dell Latitude',
                'default_price' => 15_000_000,
                'tax' => 12,
            ],
            [
                'name' => 'Mouse Logitech Wireless',
                'default_price' => 250_000,
                'tax' => 12,
            ],
            [
                'name' => 'Keyboard Mechanical',
                'default_price' => 750_000,
                'tax' => 12,
            ],
            [
                'name' => 'Monitor Samsung 24 Inch',
                'default_price' => 2_200_000,
                'tax' => 12,
            ],
        ];

        // Insert products
        foreach ($products as &$prod) {
            $prod['created_at'] = $now;
            $prod['updated_at'] = $now;
        }
        DB::table('products')->insert($products);

        // Ambil product_id dari DB
        $productIds = DB::table('products')->pluck('id', 'name')->toArray();

        // ==========================
        // SALES
        // ==========================
        $sales = [
            [
                'sale_date' => Carbon::today(),
                'invoice_number' => 'INV-IT-001',
                'total_amount' => 17_450_000,
            ],
            [
                'sale_date' => Carbon::today(),
                'invoice_number' => 'INV-IT-002',
                'total_amount' => 2_950_000,
            ],
        ];

        // Insert sales & simpan ID
        foreach ($sales as &$sale) {
            $sale['created_at'] = $now;
            $sale['updated_at'] = $now;
        }
        DB::table('sales')->insert($sales);

        // Ambil sale_id dari DB
        $saleIds = DB::table('sales')->pluck('id', 'invoice_number')->toArray();

        // ==========================
        // SALES_PRODUCTS (Pivot)
        // ==========================
        $salesProducts = [
            // Invoice 1
            [
                'sale_id' => $saleIds['INV-IT-001'],
                'product_id' => $productIds['Laptop Dell Latitude'],
                'quantity' => 1,
                'unit_price' => 15_000_000,
                'amount' => 15_000_000,
                'tax' => 12,
            ],
            [
                'sale_id' => $saleIds['INV-IT-001'],
                'product_id' => $productIds['Mouse Logitech Wireless'],
                'quantity' => 1,
                'unit_price' => 250_000,
                'amount' => 250_000,
                'tax' => 12,
            ],
            [
                'sale_id' => $saleIds['INV-IT-001'],
                'product_id' => $productIds['Keyboard Mechanical'],
                'quantity' => 1,
                'unit_price' => 750_000,
                'amount' => 750_000,
                'tax' => 12,
            ],
            // Invoice 2
            [
                'sale_id' => $saleIds['INV-IT-002'],
                'product_id' => $productIds['Monitor Samsung 24 Inch'],
                'quantity' => 1,
                'unit_price' => 2_200_000,
                'amount' => 2_200_000,
                'tax' => 12,
            ],
            [
                'sale_id' => $saleIds['INV-IT-002'],
                'product_id' => $productIds['Mouse Logitech Wireless'],
                'quantity' => 1,
                'unit_price' => 750_000,
                'amount' => 750_000,
                'tax' => 12,
            ],
        ];

        foreach ($salesProducts as &$sp) {
            $sp['created_at'] = $now;
            $sp['updated_at'] = $now;
        }

        DB::table('sales_products')->insert($salesProducts);
    }
}
