<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SuppliesMinor;

class SuppliesMinorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $supplies = [
            [ 
                'name' => 'Trila.Chicha 1Kg',
                'stock' => 25929.55,
                'consumption_weight_package' =>  0.015,
                'unid' => 12
            ],
            [ 
                'name' => 'Trila.Cereal 1Kg.',
                'stock' => 10357.36,
                'consumption_weight_package' =>  0.015,
                'unid' => 12
            ],
            [ 
                'name' => 'Trila.Chicha 2Kg.',
                'stock' => 268,
                'consumption_weight_package' =>  0.02,
                'unid' => 6
            ],
            [ 
                'name' => 'Trila.Chicha 500g.',
                'stock' => 12002,
                'consumption_weight_package' =>  0.009,
                'unid' => 24
            ],
            [ 
                'name' => 'Trila.Cereal 500g.',
                'stock' => 0,
                'consumption_weight_package' =>  0.009,
                'unid' => 24
            ],
            [ 
                'name' => 'Bilaminado Cereal.',
                'stock' => 0,
                'consumption_weight_package' =>  0.0082,
                'unid' => 12
            ],
            [ 
                'name' => 'Bilaminado Chicha',
                'stock' => 6869,
                'consumption_weight_package' =>  0.0082,
                'unid' => 12
            ],
            [ 
                'name' => 'Polietileno Crema A.',
                'stock' => 1709.21,
                'consumption_weight_package' =>  0.0077,
                'unid' => 12
            ],
            [ 
                'name' => 'Polietileno Fororo',
                'stock' => 4400.77,
                'consumption_weight_package' =>  0.0077,
                'unid' => 12
            ],
            [ 
                'name' => 'BOLSONES (Unid.)',
                'stock' => 2758.25,
                'consumption_weight_package' => 0,
                'unid' => 0
            ],
            [ 
                'name' => 'ENVOPLAS ( Kg.)',
                'stock' => 4206.76,
                'consumption_weight_package'  =>   960.0,
                'unid' => 0
            ],
        ];
       
        foreach ($supplies as $supply) {
            SuppliesMinor::firstOrCreate($supply, $supply);
        }
    }
}
