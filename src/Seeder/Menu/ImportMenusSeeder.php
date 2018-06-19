<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Seeder\Menu;


use Encore\Admin\Auth\Database\Menu;
use Illuminate\Database\Seeder;
use Mallto\Admin\Seeder\MenuSeederMaker;


class ImportMenusSeeder extends Seeder
{
    use MenuSeederMaker;

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


        $this->updateOrCreate("import_settings.index", $id,
            $order += 1, "导入配置", "fa-connectdevelop");

        $this->updateOrCreate("import_records.index", $id,
            $order += 1, "数据导入", "fa-upload");
    }
}
