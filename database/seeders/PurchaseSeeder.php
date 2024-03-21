<?php

namespace Database\Seeders;

use App\Models\Purchase;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $purchases = [];

        for ($i=0; $i < 10; $i++) {
            array_push($purchases, [
                'user_id',
                'provider_id',
                'warehouse_id',
                'date',
                'Ref',
                'GrandTotal',
                'statut',
                'paid_amount',
                'payment_statut',
            ]);
        }

        Purchase::query()->insert($purchases);
    }
}
