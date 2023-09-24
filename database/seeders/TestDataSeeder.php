<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PrimariesProduct;
use App\Models\ProductsFinal;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->setPrimariesProducts();
        $this->setProductsFinal();
    }

    public function setPrimariesProducts(){
        $products =  [
            [ 
                "name" => 'Harina de Arroz',
                "stock" => 11383.65,
            ],
            [ 
                "name" => 'Leche Completa',
                "stock" => 5000,
            ],
            [ 
                "name" => 'Azucar',
                "stock" => 186280.95,
            ],
            [ 
                "name" => 'Leche Descremada',
                "stock" => 0,
            ],
            [ 
                "name" => 'vainilla',
                "stock" => 15.30,
            ],
            [ 
                "name" => 'Premix',
                "stock" => 199.40,
            ],
            [ 
                "name" => 'Sal',
                "stock" => 36.79,
            ],   
            [ 
                "name" => 'Suero De Leche',
                "stock" => 330.35,
            ],  
       ];

        foreach ($products as $product) {
            PrimariesProduct::firstOrCreate($product, $product);
        }
    }

    public function setProductsFinal() {
        $products =  [
            [ 
                "name" => 'Nutrichicha',
                "stock" => 3660,
                "type" => 2,
                "presentation" => "1/2Kg"
            ],
            [ 
                "name" => 'Nutrichicha',
                "stock" => 560,
                "type" => 1,
                "presentation" => "1Kg",
            ],
            [ 
                "name" => 'Nutrichicha',
                "stock" => 51648,
                "type" => 2,
                "presentation" => "1Kg",
            ],
            [ 
                "name" => 'Nutrichicha',
                "stock" => 11184,
                "type" => 1,
                "presentation" => "2Kg",
            ],
            [ 
                "name" => 'Nutrichicha',
                "stock" => 67057.5,
                "type" => 1,
                "presentation" => "25Kg", 
            ],
            [ 
                "name" => 'Nutricereal',
                "stock" => 274,
                "type" => 2,
                "presentation" => "1Kg", 
            ],   
            [ 
                "name" => 'Crema de Arroz',
                "stock" => 1000,
                "type" => 1,
                "presentation" => "1Kg", 
            ],   
       ];

        foreach ($products as $product) {
            ProductsFinal::firstOrCreate($product, $product);
        }
    }

}