<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $clients = [];

        for ($i=0; $i < 5; $i++) {
            array_push($clients, [
                'name'   => fake()->name(),
                'code' => 1,
                'email' => fake()->unique()->safeEmail(),
                'country' => fake()->country(),
                'city' => fake()->city(),
                'phone' => fake()->phoneNumber(),
                'adresse' => fake()->address(),
                'tax_number' => NULL,
            ]);
        }

        Client::query()->insert($clients);
    }
}
