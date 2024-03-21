<?php

namespace Database\Seeders;

use App\Models\SaleDetail;
use Illuminate\Database\Seeder;

class SaleDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $saleDetail = [];

        for ($i=0; $i < 50; $i++) {
            array_push($saleDetail, [
                'sale_id' => fake()->numberBetween(1, 20),
                'product_id' => fake()->numberBetween(1, 50),
                'date' => fake()->date('Y-m-d'),
                'price' => fake()->numberBetween(1, 100),
                'total' => fake()->numberBetween(1, 100),
                'quantity' => fake()->numberBetween(1, 10),
            ]);
        }

        SaleDetail::query()->insert($saleDetail);
    }
}
