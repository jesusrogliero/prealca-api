<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypesIdentity;

class TypesIdentitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $identities = [
            [
                'type' => 'V',
                'name' => 'Venezolano'
            ],
            [
                'type' => 'E',
                'name' => 'Extranjero'
            ],
            [
                'type' => 'J',
                'name' => 'Juridico'
            ],
            [
                'type' => 'P',
                'name' => 'Politico'
            ],
            [
                'type' => 'G',
                'name' => 'Gubernamental'
            ]
        ];
       
        foreach ($identities as $identity) {
            TypesIdentity::firstOrCreate($identity, $identity);
        }
        
    }
}
