<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */


namespace Mallto\Admin\Grid\Exporters;

/**
 * csv 导出
 *
 * 数据导出源即为页面表格的数据源
 *
 *
 * Created by PhpStorm.
 * User: never615
 * Date: 19/03/2019
 * Time: 8:35 PM
 */
abstract class SimpleCsvExporter extends CsvExporter
{

    /**
     * 默认移除一些key开启
     *
     * @var bool
     */
    protected $forgetKeysDefault = true;


    /**
     * 一般,当数据库字段保存的是json数据时,需要再次设置该字段,避免json数据被错误转换
     *
     * 部分数据以json形式保存在数据库的一个字段下,默认会转成数组,数组的key会当做列名做导出处理
     *
     * 只支持数据库字段是json类型的在此设置
     *
     * @var array
     */
    protected $ignore2Array = [];


    /**
     * 自定义数据处理
     *
     * 这一步就是对即将到放入表格中的数据最后的加工
     *
     * @param  array $records ,orm查询结果经过array_dot后得到$records数组
     * @return array
     */
    public function customData($records)
    {
        $records = array_map(function ($record) {
            return $this->mapper($record);
        }, $records);


        //此方法必须调用
        return $this->forget($records, $this->forgetKeys(),
            $this->remainKeys(), $this->forgetKeysDefault);
    }


    /**
     * 数据加工
     *
     * 加工数据的时候要注意,每一个record输出时key的数量应该是相等的
     *
     * @param array $record
     * @return array $record,需要返回处理后的$record
     */
    public abstract function mapper($record);

    /**
     * 返回要移除的key
     *
     * @return array
     */
    public abstract function forgetKeys();

    /**
     * 返回要保留的key
     *
     * 设置此项,则forgetKeys()设置无效
     *
     * @return array
     */
    public abstract function remainKeys();


}
