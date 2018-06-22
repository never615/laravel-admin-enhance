<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Seeder;


use Illuminate\Database\Seeder;
use Mallto\Admin\Seeder\Menu\ImportMenusSeeder;

class LaravelAdminEnhanceMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ImportMenusSeeder::class);
    }
}
