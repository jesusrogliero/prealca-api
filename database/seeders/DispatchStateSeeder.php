<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DispatchesState;

class DispatchStateSeeder extends Seeder
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
            DispatchesState::firstOrCreate($state, $state);
        }
    }
}
