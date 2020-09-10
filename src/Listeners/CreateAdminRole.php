<?php
/**
 * Copyright (c) 2020. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Mallto\Admin\AdminUtils;
use Mallto\Admin\Data\Administrator;
use Mallto\Admin\Data\Permission;
use Mallto\Admin\Data\Role;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\Listeners\Events\SubjectSaved;

/**
 * User: never615 <never615.com>
 * Date: 2020/5/12
 * Time: 7:06 下午
 */
class CreateAdminRole implements ShouldQueue
{

    /**
     * 任务将被推送到的连接名称.
     *
     * @var string|null
     */
    public $queue = 'mid';


    public function handle(SubjectSaved $subjectSaved)
    {
        $subjectId = $subjectSaved->subjectId;

        $this->createOrUpdateAdminRole($subjectId);
    }


    /**
     *
     * 创建主体的时候自动创建该主体的管理员角色
     *
     * 同时赋予该主体的已购权限
     * 更新是也跟随更新
     *
     * @param $subject
     */
    protected function createOrUpdateAdminRole($subjectId)
    {
        $subject = Subject::find($subjectId);
        $name = $subject->name;

        if (Role::where([
            'subject_id' => $subjectId,
            'slug'       => 'admin',
        ])->exists()) {
            $adminRole = Role::query()
                ->where([
                    'subject_id' => $subjectId,
                    'slug'       => 'admin',
                ])->firstOrFail();

            //给角色分配权限
            if ($subject->permissions) {
                $permissionIds = $subject->permissions->pluck('id')->toArray();

                //提交过来的数组id,有一个null总是,过滤掉
                $permissionIds = array_filter($permissionIds, function ($value) {
                    if ( ! is_null($value)) {
                        return $value;
                    }
                });

                //$permissionIds添加上base权限
                $basePermissions = Permission::where('common', true)
                    ->pluck('id')
                    ->toArray();

                $permissionIds = array_merge($permissionIds, $basePermissions);

                //把subject的已购权限分配到该主体的管理员账号上
                $adminRole->permissions()->sync($permissionIds);

                AdminUtils::clearMenuCache();
            }

            return;
        }

        //创建角色
        $adminRole = Role::firstOrCreate([
            'subject_id' => $subjectId,
            'slug'       => 'admin',
        ], [
            'name' => $name . '管理员',
        ]);

        //给角色分配权限
        if ($subject->permissions) {
            $permissionIds = $subject->permissions->pluck('id')->toArray();

            //提交过来的数组id,有一个null总是,过滤掉
            $permissionIds = array_filter($permissionIds, function ($value) {
                if ( ! is_null($value)) {
                    return $value;
                }
            });

            //$permissionIds添加上base权限
            $basePermissions = Permission::where('common', true)
                ->pluck('id')
                ->toArray();

            $permissionIds = array_merge($permissionIds, $basePermissions);

            //把subject的已购权限分配到该主体的管理员账号上
            $adminRole->permissions()->sync($permissionIds);

            AdminUtils::clearMenuCache();
        }

        if ( ! Administrator::where('subject_id', $subjectId)
            ->exists()) {
            $adminUser = Administrator::firstOrCreate([
                'subject_id'     => $subjectId,
                'adminable_id'   => $subjectId,
                'adminable_type' => 'subject',
                'username'       => implode('', pinyin($name)),
                'name'           => $name . '管理',
                'password'       => bcrypt(implode('', pinyin($name))),
            ]);
            $adminUser->roles()->sync($adminRole->id);
        }
    }

}
