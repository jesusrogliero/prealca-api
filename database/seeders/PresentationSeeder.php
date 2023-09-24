<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Presentation;

class PresentationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $presentations = [
            ['name' => 'GAL'],
            ['name' => 'UNID'],
            ['name' => 'MTS'],
            ['name' => 'LTS']
        ];
        
        foreach ($presentations as $presentation) {
            Presentation::firstOrCreate($presentation, $presentation);
        }
    }
}
