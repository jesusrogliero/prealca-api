<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Province;

class ProvincesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $provinces = array(
            array('name' => 'Amazonas'),
            array('name' => 'Anzoátegui'),
            array('name' => 'Apure'),
            array('name' => 'Aragua'),
            array('name' => 'Barinas'),
            array('name' => 'Bolívar'),
            array('name' => 'Carabobo'),
            array('name' => 'Cojedes'),
            array('name' => 'Delta Amacuro'),
            array('name' => 'Falcón'),
            array('name' => 'Guárico'),
            array('name' => 'Lara'),
            array('name' => 'Mérida'),
            array('name' => 'Miranda'),
            array('name' => 'Monagas'),
            array('name' => 'Nueva Esparta'),
            array('name' => 'Portuguesa'),
            array('name' => 'Sucre'),
            array('name' => 'Táchira'),
            array('name' => 'Trujillo'),
            array('name' => 'La Guaira'),
            array('name' => 'Yaracuy'),
            array('name' => 'Zulia'),
            array('name' => 'Distrito Capital'),
            array('name' => 'Dependencias Federales')
        );

        foreach ($provinces as $province) {
            Province::firstOrCreate($province, $province);
        }
    }
}
