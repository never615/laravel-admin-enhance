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
abstract class SimpleCsvExporter extends CsvExporterBackground
{

    /**
     * 是否默认移除一些key
     *
     * @var bool
     */
    protected $forgetKeysDefault = true;

    /**
     * 一般,当数据库字段保存的是json数据时,需要在此设置该字段忽略,避免json数据被错误转换(被array_dot()转成一维数组)
     *
     * @var array
     */
    public $ignore2Array = [];

    /**
     * 是否使用$this->remainKeys()返回的key的顺序作为csv header的顺序
     *
     * @var bool
     */
    protected $useRemainKeySort = false;


    /**
     * 自定义数据处理
     *
     * 这一步就是对即将到放入表格中的数据最后的加工
     *
     * @param array $records ,orm查询结果经过array_dot后得到$records数组
     *
     * @return array
     */
    public function customData($records)
    {
        $records = array_map(function ($record) {
            $record = $this->mapper($record);

            $newRecord = [];
            if ( ! empty($remainKeys = $this->remainKeys())) {
                foreach ($remainKeys as $key) {
                    $newRecord[$key] = $record[$key];
                }
            }

            return ! empty($newRecord) ? $newRecord : $record;
        }, $records);

        //此方法必须调用
        return $this->forget($records, $this->forgetKeys(),
            $this->remainKeys(), $this->forgetKeysDefault);
    }


    /**
     * @param            $records
     *
     * @param            $tableName
     *
     * @return array
     */
    public function getHeaderRowFromRecords($records, $tableName): array
    {

        if ( ! empty($this->remainKeys()) && $this->useRemainKeySort) {
            $remainKeys = $this->remainKeys();

            return array_map(function ($value) use ($tableName) {
                return admin_translate($value, $tableName);

            }, $remainKeys);
        }

        return collect(array_first($records))->keys()->map(
            function ($key) use ($tableName) {
                return admin_translate($key, $tableName);
            }
        )->toArray();

    }


    /**
     * 数据加工
     *
     * 加工数据的时候要注意,每一个record输出时key的数量应该是相等的
     *
     * @param array $record
     *
     * @return array $record,需要返回处理后的$record
     */
    public abstract function mapper($record);


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
