<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductionsOrdersState;

class ProductionsOrdersSeeder extends Seeder
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
            ['name' => 'Guardado'],
            ['name' => 'Entregado a Almancen'],
        ];
        
        foreach ($states as $state) {
            ProductionsOrdersState::firstOrCreate($state, $state);
        }
    }
}
