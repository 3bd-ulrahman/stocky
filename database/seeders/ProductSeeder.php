<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = [];

        for ($i=0; $i < 50; $i++) {
            array_push($products, [
                'type' => Str::random(5),
                'code'=> fake()->randomNumber(5),
                'Type_barcode'=> Str::random(10),
                'name'=> fake()->name(),
                'cost'=> fake()->randomNumber(5),
                'price'=> fake()->randomNumber(5),
                'category_id'=> fake()->numberBetween(1, 10)
            ]);
        }

        Product::query()->insert($products);
    }
}
