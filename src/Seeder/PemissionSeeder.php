<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reservd.
 */

namespace Mallto\Admin\Seeder;


use Illuminate\Database\Seeder;
use Mallto\Admin\Seeder\Permission\DashboardTablesSeeder;

class PemissionSeeder extends Seeder
{
    /**
     * Run the database  seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(DashboardTablesSeeder::class);
    }
}
