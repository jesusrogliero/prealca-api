<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            ProvincesSeeder::class,
            CitiesSeeder::class,
            RolesSeeder::class,
            UserSeeder::class,
            PresentationSeeder::class,
            PositionsSeeder::class,
            PurchasesOrdersStatesSeeder::class,
            TypesIdentitiesSeeder::class,
            ProvidersSeeder::class,
            LinesProductionsSeeder::class,
            TestDataSeeder::class,
            SuppliesMinorsSeeder::class,
            FormulasSeeder::class,
            ProductionsOrdersSeeder::class,
            ProductsFinalsToWarehousesStatesSeeder::class,
            DispatchStateSeeder::class
        ]);
    }

}
