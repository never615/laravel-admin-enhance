<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Console;

use Illuminate\Console\Command;
use Mallto\Admin\Data\Menu;
use Mallto\Admin\Data\Permission;
use Mallto\Admin\Data\Subject;

/**
 * 生成path字段数据,在subject/admin_menu/admin_permissions表中
 * Class PathGeneratorCommand
 *
 * @package Mallto\Admin\Console
 */
class PathGeneratorCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'admin_enhance:path_generator';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成path字段的数据';

    /**
     * Install directory.
     *
     * @var string
     */
    protected $directory = '';


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        //subject
        Subject::chunk(500, function ($subjects) {
            foreach ($subjects as $subject) {
                if ( ! empty($subject->parent_id)) {
                    //如果存在父主体,则给path字段赋值

                    $tempParentIds = $subject->getParentSubjectIds2();
                    $tempPath = implode(".", $tempParentIds);
                    if ( ! empty($tempPath)) {
                        $subject->path = "." . $tempPath . ".";
                        $subject->save();
                        continue;
                    }
                }
                $subject->path = null;
                $subject->save();
            }
        });

        //permission
        Permission::chunk(500, function ($permissions) {
            foreach ($permissions as $permission) {
                if ( ! empty($permission->parent_id)) {
                    //如果存在父主体,则给path字段赋值

                    $temps = $permission->elderPermissions2();
                    if ( ! empty($temps)) {
                        $tempParentIds = $temps->pluck("id")->toArray();
                        $tempPath = implode(".", $tempParentIds);
                        if ( ! empty($tempPath)) {
                            $permission->path = "." . $tempPath . ".";
                            $permission->save();
                            continue;
                        }
                    }
                }
                $permission->path = null;
                $permission->save();
            }
        });

        //menu
        Menu::chunk(500, function ($menus) {
            foreach ($menus as $menu) {
                if ( ! empty($menu->parent_id)) {
                    //如果存在父主体,则给path字段赋值

                    $tempParentIds = array_pluck($menu->parentMenu2(), "id");
                    $tempPath = implode(".", $tempParentIds);
                    if ( ! empty($tempPath)) {
                        $menu->path = "." . $tempPath . ".";
                        $menu->save();
                        continue;
                    }
                }
                $menu->path = null;
                $menu->save();
            }
        });

    }

}
