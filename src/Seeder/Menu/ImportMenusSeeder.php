<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Seeder\Menu;


use Encore\Admin\Auth\Database\Menu;
use Illuminate\Database\Seeder;


class ImportMenusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $menu = Menu::where("uri", "app_secrets.index")->first();
        $order = $menu->order;

        $tempMenu = Menu::where("title", "管理")->first();


        $id = $tempMenu->id;


        Menu::updateOrCreate([
            'uri' => 'import_settings.index',
        ], [
                'parent_id' => $id,
                'order'     => $order += 1,
                'title'     => '导入配置',
                'icon'      => 'fa-connectdevelop',
            ]
        );

        Menu::updateOrCreate([
            'uri' => 'import_records.index',
        ], [
                'parent_id' => $id,
                'order'     => $order += 1,
                'title'     => '数据导入',
                'icon'      => 'fa-upload',
            ]
        );


    }
}
