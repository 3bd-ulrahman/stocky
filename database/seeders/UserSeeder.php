<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::query()->insert([
            'firstname' => 'William',
            'lastname' => 'Castillo',
            'username' => 'William Castillo',
            'email' => 'admin@example.com',
            'password' => '$2y$10$6WfhDynFHv7ffErtw1Hzh.OVqulN0Dr7XcXu6A3Exh758RWhlYFra', // password
            'avatar' => 'no_avatar.png',
            'phone' => '0123456789',
            'role_id' => 1,
            'statut' => 1,
            'is_all_warehouses' => 1,
        ]);

        $users = [];

        for ($i=0; $i < 5; $i++) {
            array_push($users, [
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

        User::query()->insert($users);
    }
}
