<?php

namespace Mallto\Admin\Seeder\FrontMenu;

use Illuminate\Database\Seeder;
use Mallto\Admin\Seeder\FrontMenuSeederMaker;

/**
 *
 *
 * 父级菜单没有对应的跳转目标,且名字中约定都有manager,如:device_manager.
 */
class FrontMapMenuSeeder extends Seeder
{

    use FrontMenuSeederMaker;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $order = 10;
        $this->delete('admin_map_admin_user');
        $this->delete('admin_map_admin_role');
        $managerMenu = $this->updateOrCreate('basic_manager', 0, $order++,
            '管理,管理,Manager', 'fa-dashboard');
        $this->updateOrCreate('admin_user', $managerMenu->id, $order++,
            '账号管理,帳號管理,Account Management', 'fa-dashboard');
        $this->updateOrCreate('admin_role', $managerMenu->id, $order++,
            '角色管理,角色管理,Role Management', 'fa-dashboard');

    }
}
