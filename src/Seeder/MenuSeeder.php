<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reservd.
 */

namespace Mallto\Admin\Seeder;


use Illuminate\Database\Seeder;
use Mallto\Admin\Seeder\Menu\DashboardSeeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database  seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(DashboardSeeder::class);
    }
}
