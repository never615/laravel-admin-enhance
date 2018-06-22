<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Seeder;


use Illuminate\Database\Seeder;
use Mallto\Admin\Seeder\Permission\DashboardTablesSeeder;
use Mallto\Admin\Seeder\Permission\ImportPermissionsSeeder;

class LaravelAdminEnhancePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ImportPermissionsSeeder::class);
        $this->call(DashboardTablesSeeder::class);

    }
}
