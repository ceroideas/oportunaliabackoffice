<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = new Role();
        $role->name = 'admin';
        $role->description = 'Administrador';
        $role->save();

        $role = new Role();
        $role->name = 'user';
        $role->description = 'Usuario';
        $role->save();

        $role = new Role();
        $role->name = 'admin_contest';
        $role->description = 'Administrador Concursal';
        $role->save();

        $role = new Role();
        $role->name = 'user_commercial';
        $role->description = 'Usuario Comercial';
        $role->save();
    }

}
