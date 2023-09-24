<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $user1 =  User::create([
            'name' => 'Jesus Miguel',
            'lastname' => 'Rogliero Colmenares',
            'email' => 'admin@api.com',
            'password' => Hash::make('Jesus24may99!'),
        ])->assignRole('Admin');

        $user2 =  User::create([
            'name' => 'Patricia',
            'lastname' => 'Rogliero Colmenares',
            'email' => 'admin1@api.com',
            'password' => Hash::make('Jesus24may99!'),
        ])->assignRole('Gerente');


        $user3 =  User::create([
            'name' => 'Fabio',
            'lastname' => 'Trentin',
            'email' => 'admin2@api.com',
            'password' => Hash::make('manager')
        ])->assignRole('Coordinador de Almacen');

        
        $user4 =  User::create([
            'name' => 'Jesus',
            'lastname' => 'Rogliero',
            'email' => 'admin3@api.com',
            'password' => Hash::make('manager')
        ])->assignRole('Coordinador de Producción');

    }
}
