<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data\Traits;

/**
 * Created by PhpStorm.
 * User: never615
 * Date: 24/03/2017
 * Time: 7:51 PM
 */

use Illuminate\Support\Facades\Schema;
use Mallto\Admin\SubjectUtils;

/**
 * 在这里处理查询作用域
 * 用来实现不同主体查询不同的数据内容.需要实现不同主体加载不同数据的model引入DynamicData即可
 *
 * Created by PhpStorm.
 * User: never615
 * Date: 24/03/2017
 * Time: 4:40 PM
 */
trait DynamicData
{

    /**
     * 动态设定查询数据范围
     *
     * 项目拥有者具有查看全部业务数据的能力
     * 子主体只能查看自己拥有的数据
     *
     * @param $query
     */
    public function scopeDynamicData($query)
    {
        if (Schema::hasColumn($this->getTable(), 'subject_id')) {
            //1.获取当前登录账户属于哪一个主体
            $currentSubject = SubjectUtils::getSubject();
            //2.获取当前主体的所有子主体
            $ids = $currentSubject->getChildrenSubject();

            //查询管理账户是否设置了manager_subject_ids暂时用不到,先屏蔽

            //查询管理账户是否设置了manager_subject_ids
//            $managerSubjectIds = $adminUser->manager_subject_ids;
//            if (!empty($managerSubjectIds)) {
//                $tempSubject = new Subject();
//                $tempSubjectIds = $managerSubjectIds;
//
//                foreach ($managerSubjectIds as $managerSubjectId) {
//                    $tempSubjectIds = array_merge($tempSubjectIds,
//                        $tempSubject->getChildrenSubject($managerSubjectId));
//                }
//
//                $tempSubjectIds = array_merge($tempSubjectIds, $ids);
//                $ids = array_unique($tempSubjectIds);
//            }

            //3.限定查询范围为所有子主体

            $query->whereIn('subject_id', $ids)->orderBy('id');
        }
    }

}
