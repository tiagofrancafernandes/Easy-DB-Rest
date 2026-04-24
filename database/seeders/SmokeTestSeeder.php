<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SmokeTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            price REAL NOT NULL,
            stock INTEGER NOT NULL DEFAULT 0,
            active INTEGER NOT NULL DEFAULT 1,
            created_at TEXT DEFAULT (datetime(\'now\'))
        )');

        DB::table('products')->insertOrIgnore([
            ['name' => 'Widget A', 'price' => 9.99, 'stock' => 100, 'active' => 1],
            ['name' => 'Widget B', 'price' => 19.99, 'stock' => 50, 'active' => 1],
            ['name' => 'Gadget Pro', 'price' => 49.99, 'stock' => 25, 'active' => 1],
            ['name' => 'Old Model', 'price' => 4.99, 'stock' => 0, 'active' => 0],
            ['name' => 'Deluxe Kit', 'price' => 99.99, 'stock' => 10, 'active' => 1],
        ]);

        DB::statement('CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER NOT NULL,
            quantity INTEGER NOT NULL,
            total REAL NOT NULL,
            placed_at TEXT DEFAULT (datetime(\'now\')),
            FOREIGN KEY (product_id) REFERENCES products(id)
        )');

        DB::table('orders')->insertOrIgnore([
            ['product_id' => 1, 'quantity' => 3, 'total' => 29.97],
            ['product_id' => 2, 'quantity' => 1, 'total' => 19.99],
            ['product_id' => 3, 'quantity' => 2, 'total' => 99.98],
            ['product_id' => 5, 'quantity' => 1, 'total' => 99.99],
        ]);
    }
}
