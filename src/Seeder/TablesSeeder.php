<?php

namespace Mallto\Admin\Seeder;

use Illuminate\Database\Seeder;
use Mallto\Admin\Seeder\FrontMenu\FrontMapMenuSeeder;

class TablesSeeder extends Seeder
{

    /**
     * Run the database  seeds.
     *
     * @return void
     */
    public function run()
    {

        $this->call([
            MenuTablesSeeder::class,
            PermissionTablesSeeder::class,
            ImportSettingSeeder::class,
            FrontMapMenuSeeder::class,
        ]);
    }
}
