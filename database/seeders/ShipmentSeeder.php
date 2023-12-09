<?php

namespace Database\Seeders;

use App\Models\Casts\ShipmentStatus;
use App\Models\Sale;
use App\Models\Shipment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sales = Sale::query()->get(['id', 'user_id']);
        $sale = fake()->randomElement($sales);

        $shipments = [];

        for ($i=0; $i < 50; $i++) {
            array_push($shipments, [
                'user_id' => $sale['user_id'],
                'sale_id' => $sale['id'],
                'Ref' => Str::random(5),
                'status' => fake()->randomElement(ShipmentStatus::cases())->value
            ]);
        }

        Shipment::query()->insert($shipments);
    }
}
