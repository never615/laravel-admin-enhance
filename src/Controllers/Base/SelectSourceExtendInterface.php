<?php
/**
 * Copyright (c) 2020. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Base;

/**
 * User: never615 <never615.com>
 * Date: 2020/7/20
 * Time: 4:14 下午
 */
interface SelectSourceExtendInterface
{

    /**
     *
     * 分批分页加载数据,支持搜索,支持多个id直接限定查询结果
     *
     * 方便下级依赖库添加数据源
     *
     * @param $key
     * @param array|int $id 限定查询的id数组 或者 int 的id
     * @param $childSubjectIds
     * @param $q
     * @param $perPage
     * @param $adminUser
     * @param $fatherValue
     */
    public function addDataSource($key, $id, $childSubjectIds, $q, $perPage, $adminUser, $fatherValue);


    /**
     * 方便下级依赖库添加数据源
     *
     * @param $q
     * @param $perPage
     * @param $childSubjectIds
     * @param $fatherValue
     */
    public function addLoad($q, $perPage, $childSubjectIds, $fatherValue);

}
