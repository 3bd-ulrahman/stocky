<?php

namespace Database\Seeders;

use App\Models\Locale;
use Illuminate\Database\Seeder;

class LocaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Locale::query()->insert([
            [
                'name' => 'English',
                'abbreviation' => 'en',
                'flag' => 'gb',
                'status' => 1,
            ],
            [
                'name' => 'Arabic',
                'abbreviation' => 'ar',
                'flag' => 'eg',
                'status' => 1,
            ]
        ]);
    }
}
