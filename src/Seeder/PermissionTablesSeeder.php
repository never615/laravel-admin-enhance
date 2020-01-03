<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Seeder;

use Illuminate\Database\Seeder;
use Mallto\Admin\Seeder\Permission\AdminmanagerSeeder;
use Mallto\Admin\Seeder\Permission\AdminUserGroupPermissionSeeder;
use Mallto\Admin\Seeder\Permission\ImportPermissionsSeeder;

class PermissionTablesSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ImportPermissionsSeeder::class);
        $this->call(AdminmanagerSeeder::class);
        $this->call(AdminUserGroupPermissionSeeder::class);

    }
}
