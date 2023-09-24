<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Line;

class LinesProductionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $lines = [
            ['name' => 'Linea 1'],
            ['name' => 'Linea 2'],
        ];
        
        foreach ($lines as $line) {
            Line::firstOrCreate($line, $line);
        }
    }

}
