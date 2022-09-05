<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Seeder;

use Illuminate\Database\Seeder;
use Mallto\Admin\Seeder\Menu\AdminManagerMenuSeeder;
use Mallto\Admin\Seeder\Menu\AdminUserGroupMenuSeeder;
use Mallto\Admin\Seeder\Menu\DashboardMenuSeeder;
use Mallto\Admin\Seeder\Menu\ImportMenusSeeder;
use Mallto\Admin\Seeder\Menu\OperationMenuSeeder;
use Mallto\Admin\Seeder\Menu\SystemManagerMenuSeeder;

class MenuTablesSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(AdminManagerMenuSeeder::class);
        $this->call(DashboardMenuSeeder::class);
        $this->call(SystemManagerMenuSeeder::class);
        $this->call(OperationMenuSeeder::class);

    }
}
