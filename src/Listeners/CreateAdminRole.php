<?php
/**
 * Copyright (c) 2020. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Mallto\Admin\CacheUtils;
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
    public $queue = 'high';

    /**
     * 延迟3s 执行,以便其他库在主体创建后修改主体关联的权限
     *
     * 处理任务的延迟时间.
     *
     * @var int
     */
    public $delay = 1;


    public function handle(SubjectSaved $subjectSaved)
    {
        $subjectId = $subjectSaved->subjectId;
        $new = $subjectSaved->new ?? false;
        $force = $subjectSaved->force ?? false;
        $data = $subjectSaved->data ?? [];

        if (config('other.auto_create') || $force) {
            $this->createOrUpdateAdminRole($subjectId, $new, $data);
        }
    }


    /**
     *
     * 创建主体的时候自动创建该主体的管理员角色
     *
     * 同时赋予该主体的已购权限
     * 更新是也跟随更新
     *
     * @param       $subjectId
     * @param       $new
     * @param array $data
     */
    protected function createOrUpdateAdminRole($subjectId, $new, $data = [])
    {
        $subject = Subject::find($subjectId);
        $name = $subject->name;

        if (Role::where([
            'subject_id' => $subjectId,
            'slug' => 'admin',
        ])->exists()) {
            $adminRole = Role::query()->where([
                'subject_id' => $subjectId,
                'slug' => 'admin',
            ])->firstOrFail();

            //给角色分配权限
            if ($subject->permissions) {
                $permissionIds = $subject->permissions->pluck('id')->toArray();

                //提交过来的数组id,有一个null总是,过滤掉
                $permissionIds = array_filter($permissionIds, function ($value) {
                    if (!is_null($value)) {
                        return $value;
                    }
                });

                //$permissionIds添加上base权限
                $basePermissions = Permission::where('common', true)->pluck('id')->toArray();

                $permissionIds = array_merge($permissionIds, $basePermissions);

                //把subject的已购权限分配到该主体的管理员账号上
                $adminRole->permissions()->sync($permissionIds);

                CacheUtils::clearMenuCache();
            }

            return;
        }

        //创建角色
        $adminRole = Role::firstOrCreate([
            'subject_id' => $subjectId,
            'slug' => 'admin',
        ], [
            'name' => $name . '管理员',
        ]);

        //给角色分配权限
        if ($subject->permissions) {
            $permissionIds = $subject->permissions->pluck('id')->toArray();

            //提交过来的数组id,有一个null总是,过滤掉
            $permissionIds = array_filter($permissionIds, function ($value) {
                if (!is_null($value)) {
                    return $value;
                }
            });

            //$permissionIds添加上base权限
            $basePermissions = Permission::where('common', true)->pluck('id')->toArray();

            $permissionIds = array_merge($permissionIds, $basePermissions);

            //把subject的已购权限分配到该主体的管理员账号上
            $adminRole->permissions()->sync($permissionIds);

            CacheUtils::clearMenuCache();
        }

        $name = $data['name'] ?? $name . '管理';

        if (!Administrator::where('subject_id', $subjectId)->where('name', $name)->exists()) {
            $username = $data['username'] ?? implode('', pinyin($name));
            $password = bcrypt($data['password'] ?? implode('', pinyin($name)));
            $adminUser = Administrator::firstOrCreate([
                'subject_id' => $subjectId,
                'adminable_id' => $subjectId,
                'adminable_type' => 'subject',
                'username' => $username,
                'name' => $name,
                'password' => $password,
                'mobile' => $data['mobile'] ?? null,
            ]);
            $adminUser->roles()->sync($adminRole->id);
        }

    }

}
