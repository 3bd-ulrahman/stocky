<?php

namespace Database\Seeders;

use App\Models\SaleReturn;
use Illuminate\Database\Seeder;

class SaleReturnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $saleReturns = [];

        for ($i=0; $i < 5; $i++) {
            array_push($saleReturns, [
                'firstname' => fake()->firstName(),
                'lastname' => fake()->lastName(),
                'username' => fake()->userName(),
                'email' => fake()->unique()->safeEmail(),
                'password' => fake()->password(),
                'is_representative' => fake()->randomElement([0, 1]),
                'avatar' => 'no_avatar.png',
                'phone' => fake()->phoneNumber(),
                'role_id' => fake()->numberBetween(1, 5),
                'statut' => '1',
                'is_all_warehouses' => fake()->boolean(),
            ]);
        }

        SaleReturn::query()->insert($saleReturns);
    }
}
