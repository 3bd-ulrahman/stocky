<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Warehouse;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $warehouses = [];

        for ($i=0; $i < 10; $i++) {
            array_push($warehouses, [
                'name' => fake()->name(),
                'city' => fake()->city(),
                'mobile' => fake()->phoneNumber()
            ]);
        }

        Warehouse::query()->insert($warehouses);
    }
}
