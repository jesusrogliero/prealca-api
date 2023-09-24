<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Position;

class PositionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $positions = [
            ['name' => 'DIRECTOR DE LINEA'],
            ['name' => '(GRADO V) VIGILANTE'],
            ['name' => '(GRADO IV) CHOFER'],
            ['name' => '(GRADO V) AYUDANTE DE ALAMACEN'],
            ['name' => 'Coordinador Central'],
            ['name' => 'BOLSA DE TRABAJO'],
            ['name' => '(P I) PROFESIONAL I'],
            ['name' => 'Gerente de Planta'],
        ];
        
        foreach ($positions as $position) {
            Position::firstOrCreate($position, $position);
        }
    }

}

