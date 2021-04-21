<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Domain\Export;

use Mallto\Admin\Grid\Exporters\SimpleCsvExporter;
use Mallto\Mall\Constants;
use Mallto\Mall\Data\AdminUser;

class AdminPermissionExporter extends SimpleCsvExporter
{

    /**
     * 数据加工
     *
     * 加工数据的时候要注意,每一个record输出时key的数量应该是相等的
     *
     * @param array $record
     *
     * @return array $record,需要返回处理后的$record
     */
    public function mapper($record)
    {
        return $record;
    }


    /**
     * 返回要移除的key
     *
     * 参数可以传入关联数据的**模型名**来忽略该模型下的全部数据,
     * 如导出user数据的时候,传入member会忽略user关联的member对象下的所有字段.
     *
     * 也可以使用member.name移除关联模型的指定字段
     *
     *
     * 默认移除了一些字段参见  @return array
     *
     * @var $this ->defaultForgetKeys
     *
     */
    public function forgetKeys()
    {
    }


    /**
     * 返回要保留的key
     *
     * 设置此项,则forgetKeys()设置无效
     *
     * @return array
     */
    public function remainKeys()
    {
        return [
            'id',
            'name',
            'slug',
        ];
    }
}
