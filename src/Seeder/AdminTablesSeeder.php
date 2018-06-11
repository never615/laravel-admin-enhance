<?php

namespace Malto\Admin\Seeder;

use Illuminate\Database\Seeder;
use Mallto\Admin\Seeder\BaseTablesSeeder;
use Mallto\Admin\Seeder\MenuTablesSeeder;
use Mallto\Admin\Seeder\Permission\DashboardTablesSeeder;
use Mallto\Admin\Seeder\Permission\ImportPermissionsSeeder;
use Mallto\Admin\Seeder\VideoMenuSeeder;

class AdminTablesSeeder extends Seeder
{
    /**
     * Run the database  seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(BaseTablesSeeder::class);
        $this->call(MenuTablesSeeder::class);
        $this->call(Menu2TablesSeeder::class);
        $this->call(VideoMenuSeeder::class);

        $this->call(ImportPermissionsSeeder::class);
        $this->call(DashboardTablesSeeder::class);
    }
}
