<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [];

        for ($i=0; $i < 10; $i++) {
            array_push($categories, [
                'code' => fake()->randomNumber(9),
                'en' => ['name' => fake()->name()],
                'ar' => ['name' => fake('ar_JO')->name()]
            ]);
        }

        collect($categories)->each(function ($category) {
            Category::query()->create($category);
        });
    }
}
