<?php

namespace Mallto\Admin\Seeder;

use Illuminate\Database\Seeder;
use Mallto\Admin\Seeder\Menu\AdminManagerMenuSeeder;
use Mallto\Admin\Seeder\Menu\DashboardMenuSeeder;
use Mallto\Admin\Seeder\Permission\AdminmanagerSeeder;
use Mallto\Admin\Seeder\Permission\ImportPermissionsSeeder;

class TablesSeeder extends Seeder
{
    /**
     * Run the database  seeds.
     *
     * @return void
     */
    public function run(MenuTablesSeeder $menuTablesSeeder,PermissionTablesSeeder $permissionTablesSeeder)
    {
        $menuTablesSeeder->run();
        $permissionTablesSeeder->run();
    }
}
