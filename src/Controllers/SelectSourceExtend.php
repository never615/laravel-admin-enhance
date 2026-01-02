<?php
/**
 * Copyright (c) 2021. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Mallto\Admin\Controllers\Base\SelectSourceExtendInterface;
use Mallto\Admin\Data\Role;

/**
 * User: never615 <never615.com>
 * Date: 2021/2/7
 * Time: 1:56 上午
 */
class SelectSourceExtend implements SelectSourceExtendInterface
{

    /**
     * 方便下级依赖库添加数据源
     * 分批分页加载数据,支持搜索,支持多个id直接限定查询结果
     *
     *
     * @param $key
     * @param array|int $id 限定查询的id数组 或者 int 的id
     * @param $childSubjectIds
     * @param $q
     * @param $perPage
     * @param $adminUser
     * @param $fatherValue
     * @return
     */
    public function addDataSource($key, $id, $childSubjectIds, $q, $perPage, $adminUser, $fatherValue)
    {
        switch ($key) {
            case 'role':
                return $this->roleSelect($id, $childSubjectIds, $q, $perPage, $adminUser);
            case 'front_role':
                return $this->roleSelect($id, $childSubjectIds, $q, $perPage, $adminUser, true);

        }
    }


    /**
     * 方便下级依赖库添加数据源
     *
     * @param $q
     * @param $perPage
     * @param $childSubjectIds
     * @param $fatherValue
     * @return Builder[]|Collection|void
     */
    public function addLoad($q, $perPage, $childSubjectIds, $fatherValue)
    {
    }

    private function roleSelect($id, $childSubjectIds, $q, $perPage, $adminUser, $front = false)
    {
        $query = Role::query();

        if ($front) {
            $query->where('admin_roles.pure_front', 1);
        }


        if (!$adminUser || !$adminUser->isOwner()) {
            $query->select(DB::raw('admin_roles.id,admin_roles.name as text'));
        } else {
            $query->select(DB::raw("admin_roles.id,admin_roles.name||'-('||subjects.name||')' as text"));
        }


        if (!is_null($id)) {
            return $query->findOrFail($id);
        } else {
            $query->join('subjects', 'subjects.id', 'subject_id')
                ->orderBy('admin_roles.created_at', 'desc')
                ->whereIn('admin_roles.subject_id', $childSubjectIds);

            if ($q)
                $query->where('admin_roles.name', '~*', "$q");
        }


        return $query->paginate($perPage, ['id', 'text']);

    }

}


