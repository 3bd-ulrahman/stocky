<?php

namespace Database\Seeders;

use App\Models\Provider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $providers = [];

        for ($i=0; $i < 10; $i++) {
            array_push($providers, [
                'name' => fake()->name(),
                'code' => Str::random(8),
                'adresse' => fake()->address(),
                'phone' => fake()->phoneNumber(),
                'country' => fake()->country(),
            ]);
        }

        Provider::query()->insert($providers);
    }
}
