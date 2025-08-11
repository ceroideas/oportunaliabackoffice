<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('provinces')->insert([
                ['name' => "Cádiz"],
                ['name' => "Álava"],
                ['name' => "Albacete"],
                ['name' => "Albacete"],
                ['name' => "Almería"],
                ['name' => "Asturias"],
                ['name' => "Ávila"],
                ['name' => "Badajoz"],
                ['name' => "Barcelona"],
                ['name' => "Burgos"],
                ['name' => "Cáceres"],
                ['name' => "Cantabria"],
                ['name' => "Castellón"],
                ['name' => "Ciudad Real"],
                ['name' => "Córdoba"],
                ['name' => "Cuenca"],
                ['name' => "Girona"],
                ['name' => "Granada"],
                ['name' => "Guadalajara"],
                ['name' => "Guipúzcoa"],
                ['name' => "Huelva"],
                ['name' => "Huesca"],
                ['name' => "Islas Baleares"],
                ['name' => "Jaén"],
                ['name' => "A Coruña"],
                ['name' => "La Rioja"],
                ['name' => "Las Palmas"],
                ['name' => "León"],
                ['name' => "Lleida"],
                ['name' => "Lugo"],
                ['name' => "Madrid"],
                ['name' => "Málaga"],
                ['name' => "Murcia"],
                ['name' => "Navarra"],
                ['name' => "Ourense"],
                ['name' => "Palencia"],
                ['name' => "Pontevedra"],
                ['name' => "Salamanca"],
                ['name' => "Santa Cruz de Tenerife"],
                ['name' => "Segovia"],
                ['name' => "Sevilla"],
                ['name' => "Soria"],
                ['name' => "Tarragona"],
                ['name' => "Teruel"],
                ['name' => "Toledo"],
                ['name' => "Valencia"],
                ['name' => "Valladolid"],
                ['name' => "Vizcaya"],
                ['name' => "Zamora"],
                ['name' => "Zaragoza"],
                ['name' => "Ceuta"],
                ['name' => "Melilla"]
                ]
        );
    }
}
