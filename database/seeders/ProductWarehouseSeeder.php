<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductWarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $productWarehouse = [];

        for ($i=0; $i < 10; $i++) {
            array_push($productWarehouse, [
                'product_id' => fake()->numberBetween(1, 50),
                'warehouse_id' => fake()->numberBetween(1, 10),
                'qte' => fake()->numberBetween(1, 20)
            ]);
        }

        DB::table('product_warehouse')->insert($productWarehouse);
    }
}
