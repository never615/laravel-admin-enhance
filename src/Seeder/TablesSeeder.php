<?php

namespace Mallto\Admin\Seeder;

use Illuminate\Database\Seeder;

class TablesSeeder extends Seeder
{

    /**
     * Run the database  seeds.
     *
     * @return void
     */
    public function run(MenuTablesSeeder $menuTablesSeeder, PermissionTablesSeeder $permissionTablesSeeder)
    {
        $menuTablesSeeder->run();
        $permissionTablesSeeder->run();
    }
}
