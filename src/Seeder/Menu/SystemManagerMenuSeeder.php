<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Seeder\Menu;

use Illuminate\Database\Seeder;
use Mallto\Admin\Seeder\MenuSeederMaker;

/**
 * Created by PhpStorm.
 * User: never615 <never615.com>
 * Date: 2018/11/23
 * Time: 4:39 PM
 */
class SystemManagerMenuSeeder extends Seeder
{
    use MenuSeederMaker;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $order = 101;

        $systemManagerMenu = $this->updateOrCreate(
            "system_manager", 0, $order++, "系统管理", "fa-windows");

        $order = $systemManagerMenu->order;



        $this->updateOrCreate(
            "logs.index", $systemManagerMenu->id,
            $order++, "操作日志", "fa-history");

        $this->updateOrCreate(
            "import_settings.index", $systemManagerMenu->id,
            $order++, "导入配置", "fa-connectdevelop");



    }

}