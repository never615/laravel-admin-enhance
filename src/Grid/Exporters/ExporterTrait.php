<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Exporters;

/**
 * 辅助导出功能使用的工具
 *
 * Created by PhpStorm.
 * User: never615
 * Date: 29/03/2017
 * Time: 7:48 PM
 */
trait ExporterTrait
{
    /**
     * @param            $records
     *
     * @param            $tableName
     * @return array
     */
    public function getHeaderRowFromRecords($records, $tableName): array
    {
        $titles = collect(array_first($records))->keys()->map(
            function ($key) use ($tableName) {
                $tempKeys = explode(".", $key);
                $tempKeysCount = count($tempKeys);
                if ($tempKeysCount > 1) {
                    $tableName = $tempKeys[$tempKeysCount - 2];
                }

                return admin_translate($tempKeys[$tempKeysCount - 1], $tableName);
            }
        );


        return $titles->toArray();
    }

    /**
     * @param  $record
     *
     * @return array
     */
    public function getFormattedRecord($record)
    {
        return array_map(function ($value) {
            if (is_array($value) && count($value) <= 0) {
                return null;
            } else {
                return $value;
            }
        }, $record);
    }

    public function getFileName($extension = "")
    {
        $tableName = $this->getTable();
        $now =  date('Y-m-d H:i:s');
        return admin_translate("table.".$tableName)."_".$now."_".substr(time(), 5).$extension;
    }


    /**
     * 自定义数据处理
     *
     * 这一步就是对即将到放入表格中的数据最后的加工
     *
     * @param  $records ,orm查询结果经过array_dot后得到$records
     * @return array
     */
    public function customData($records)
    {
        //此方法必须调用
        return $this->forget($records, [
        ]);
    }


    /**
     * 一般用来处理关联对象的属性
     *
     * @param $records
     * @param $keys
     * @param $callback
     * @return array
     */
    public function transform($records, $keys, $callback)
    {
        $newRecords = [];
        foreach ($records as $record) {
            foreach ($keys as $key) {
                $record = $callback($record, $key);
            }
            $newRecords[] = $record;
        }

        return $newRecords;
    }


    /**
     * Remove an item from the collection/array by key.
     *
     * @param              $records
     * @param array|string $keys
     * @param bool         $default
     * @return array
     */
    public function forget($records, $keys = [], $default = true)
    {
        if ($default) {
            $keys = array_merge([
                "images",
                "image",
                "icon",
                "logo",
                "deleted_at",
                "top_subject_id",
                "admin_user_id",
                "subject_id",
            ], $keys);
        }

        return array_map(function ($record) use ($keys) {
            foreach ((array) $keys as $key) {
                foreach ($record as $recordKey => $recordValue) {
                    if (starts_with($recordKey, trim($key, '.').".")) {
                        unset($record[$recordKey]);
                    }
                }

                unset($record[$key]);
            }

            return $record;
        }, $records);


//
//        $records = $records->map(function (Model $record) use ($keys) {
//
//            foreach ((array) $keys as $key) {
//                unset($record[$key]);
//            }
//
//            \Log::info($record);
////            $record = array_dot($record->toArray());
//
//            foreach ((array) $keys as $key) {
//                unset($record[$key]);
//            }
//
//            return $record;
//        });
//
//
//        return $records;
    }


}
