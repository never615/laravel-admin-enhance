<?php

namespace Mallto\Admin\Seeder\Menu;

use Illuminate\Database\Seeder;
use Mallto\Admin\Seeder\MenuSeederMaker;

class DashboardMenuSeeder extends Seeder
{

    use MenuSeederMaker;


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $order = 1;

        $this->updateOrCreate(
            "dashboard", 0, $order++, "Dashboard", "fa-bar-chart");

    }
}
