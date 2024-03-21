<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        $this->call([
            // BrandSeeder::class,
            // ClientSeeder::class,
            // CategorySeeder::class,
            // CurrencySeeder::class,
            // SettingSeeder::class,
            // ServerSeeder::class,
            // PermissionsSeeder::class,
            // RoleSeeder::class,
            // UserSeeder::class,
            // RoleUserSeeder::class,
            // PermissionRoleSeeder::class,
            // WarehouseSeeder::class,
            // SaleSeeder::class,
            // LocaleSeeder::class,
            // UserWarehouseSeeder::class,
            // ProductSeeder::class,
            // ProductWarehouseSeeder::class,
            // PaymentGatewaySeeder::class,
            // ProviderSeeder::class,
            // PurchaseSeeder::class,
            // SaleReturnSeeder::class,
            SaleDetailSeeder::class
        ]);
        Schema::enableForeignKeyConstraints();
    }
}
