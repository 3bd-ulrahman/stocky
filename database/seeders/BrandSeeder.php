<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $brands = [];

        for ($i=0; $i < 10; $i++) {
            array_push($brands, [
                'en' => ['name' => fake()->name()],
                'ar' => ['name' => fake('ar_JO')->name()],
                'description' => fake()->text(),
                'image' => fake()->imageUrl(),
            ]);
        }

        collect($brands)->each(function ($brand) {
            Brand::query()->create($brand);
        });
    }
}
