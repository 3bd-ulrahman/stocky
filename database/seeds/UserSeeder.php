<?php

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
       // Insert some stuff
        DB::table('users')->insert(
            array(
                'id' => 1,
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
            )
        );
    }
}
