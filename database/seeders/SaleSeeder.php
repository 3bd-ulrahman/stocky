<?php

namespace Database\Seeders;

use App\Models\Sale;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sales = [];

        for ($i=0; $i < 20; $i++) {
            array_push($sales, [
                'user_id' => fake()->numberBetween(1, 5),
                'date' => fake()->dateTimeBetween('January 1'),
                'Ref' => fake()->text(20),
                'client_id' => fake()->numberBetween(1, 5),
                'warehouse_id' => fake()->numberBetween(1, 5),
                'GrandTotal' => fake()->numberBetween(1, 1000),
                'payment_statut' => 'paid',
                'statut' => fake()->text(20)
            ]);
        }

        Sale::query()->insert($sales);
    }
}
