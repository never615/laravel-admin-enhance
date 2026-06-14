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
class ConfigManagerMenuSeeder extends Seeder
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

        $configCenterMenu = $this->updateOrCreate(
            'config_center', 0, $systemManagerMenu->order + 1, '配置中心', 'fa-sliders');

        $traditionalConfigMenu = $this->updateOrCreate(
            'traditional_configs', $configCenterMenu->id, 90, '传统配置', 'fa-archive');

        $this->updateOrCreate(
            'subject_configs.index', $traditionalConfigMenu->id,
            10, '动态配置', 'fa-assistive-listening');

        // 项目配置
        $this->updateOrCreate(
            'subject_settings.index', $traditionalConfigMenu->id, 20, '项目配置', 'fa-server');


    }

}
