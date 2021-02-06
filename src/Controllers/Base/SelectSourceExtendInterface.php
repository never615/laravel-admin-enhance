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
     * 方便下级依赖库添加数据源
     *
     * @param $key
     * @param $id
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
