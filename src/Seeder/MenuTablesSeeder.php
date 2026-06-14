<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Seeder;

use Illuminate\Database\Seeder;
use Mallto\Admin\Seeder\Menu\AdminManagerMenuSeeder;
use Mallto\Admin\Seeder\Menu\ConfigManagerMenuSeeder;
use Mallto\Admin\Seeder\Menu\DashboardMenuSeeder;
use Mallto\Admin\Seeder\Menu\LogManagerMenuSeeder;
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
        $this->call([
            AdminManagerMenuSeeder::class,
            DashboardMenuSeeder::class,
            SystemManagerMenuSeeder::class,
            OperationMenuSeeder::class,
            ConfigManagerMenuSeeder::class,
            LogManagerMenuSeeder::class
        ]);

    }
}
