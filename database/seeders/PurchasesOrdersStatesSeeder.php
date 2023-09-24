<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PurchasesOrdersState;

class PurchasesOrdersStatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    
       $states =  [
            [ "name" => 'Pendiente' ],
            [ "name" => 'Aprobada']
       ];

        foreach ($states as $state) {
            PurchasesOrdersState::firstOrCreate($state, $state);
        }
    }
}
