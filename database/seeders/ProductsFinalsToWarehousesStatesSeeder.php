<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductsFinalsToWarehousesState;

class ProductsFinalsToWarehousesStatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    { 
        
        $states = [
            ['name' => 'Pendiente'],
            ['name' => 'Ingresado'],
        ];
        
        foreach ($states as $state) {
            ProductsFinalsToWarehousesState::firstOrCreate($state, $state);
        }
        
    }
}
