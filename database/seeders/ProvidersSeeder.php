<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Provider;

class ProvidersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $providers = [
            [
                'name' => 'Jesus Rogliero',
                'identity' => 27453038,
                'type_identity_id' => 1,
                'address' => 'Urb Palmarito',
                'phone' => '04144519031'
            ],
        ];
        
        foreach ($providers as $provider) {
            Provider::firstOrCreate($provider, $provider);
        }
    }
}
