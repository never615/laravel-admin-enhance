<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Malto\Admin\Seeder;


use Encore\Admin\Auth\Database\Menu;
use Illuminate\Database\Seeder;

class Menu2TablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menu = Menu::where("title", "文件管理")->first();


        if ($menu) {
            return;
        }




        $menu = Menu::where("title", "报表管理")->first();

        Menu::create([
            'parent_id' => $menu->parent_id,
            'order'     => $menu->order + 1,
            'title'     => '文件管理',
            'icon'      => 'fa-file',
            'uri'       => 'uploads.index',
        ]);
    }
}
