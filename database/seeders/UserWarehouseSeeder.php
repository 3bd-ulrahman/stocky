<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserWarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userWarehouse = [];
        $userIds = range(1, 5);
        $warehouseIds = range(1, 20);

        foreach ($userIds as $userId) {
            array_push($userWarehouse, [
                'user_id' => $userId,
                'warehouse_id' => fake()->randomElement($warehouseIds),
                'created_at' - now()->format('Y-m-d H:i:s'),
            ]);
        }

        DB::table('user_warehouse')->insert($userWarehouse);
    }
}
