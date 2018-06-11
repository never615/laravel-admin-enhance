<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Seeder;


use Illuminate\Database\Seeder;
use Mallto\Admin\Seeder\Menu\ImportMenusSeeder;
use Mallto\Admin\Seeder\Permission\DashboardTablesSeeder;
use Mallto\Admin\Seeder\Permission\ImportPermissionsSeeder;
use Mallto\Mall\Seeder\Menu\CommondityMenusSeeder;
use Mallto\Mall\Seeder\Menu\MemberMenusSeeder;
use Mallto\Mall\Seeder\Menu\Operate1;
use Mallto\Mall\Seeder\Menu\Operate2;
use Mallto\Mall\Seeder\Menu\ParkMenusSeeder;

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
