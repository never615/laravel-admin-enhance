<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Seeder;


use Illuminate\Database\Seeder;
use Mallto\Admin\Seeder\Menu\ImportMenusSeeder;
use Mallto\Mall\Seeder\Menu\CommondityMenusSeeder;
use Mallto\Mall\Seeder\Menu\MemberMenusSeeder;
use Mallto\Mall\Seeder\Menu\Operate1;
use Mallto\Mall\Seeder\Menu\Operate2;
use Mallto\Mall\Seeder\Menu\ParkMenusSeeder;

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
