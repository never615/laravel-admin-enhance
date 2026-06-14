<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Seeder\Menu;

use Illuminate\Database\Seeder;
use Mallto\Admin\Data\Menu;
use Mallto\Admin\Seeder\MenuSeederMaker;

/**
 * Created by PhpStorm.
 * User: never615 <never615.com>
 * Date: 2018/11/23
 * Time: 4:39 PM
 */
class LogManagerMenuSeeder extends Seeder
{

    use MenuSeederMaker;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $systemManagerMenu = Menu::where('uri', 'system_manager')->first();

        $logCenterMenu = $this->updateOrCreate(
            "log_center", 0, $systemManagerMenu->order + 3, "日志中心", "fa-list-alt");

        $this->updateOrCreate(
            'logs.index', $logCenterMenu->id,
            10, '操作日志', 'fa-history');

        $this->updateOrCreate(
            "operation_log_dictionarys.index", $logCenterMenu->id, 20, "操作日志字典", "fa-line-chart");


    }

}
