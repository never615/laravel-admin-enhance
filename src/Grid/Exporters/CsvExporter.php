<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Exporters;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * csv 导出
 *
 * 数据导出源即为页面表格的数据源
 *
 * 请直接使用SimpleCsvExporter,该类因历史原因有些代码在使用所以保留.
 *
 *
 * 相比larvel-admin库的csv导出:
 * 1. 导出文件名:表名+时间(表名支持自动翻译)
 * 2. 导出数据,支持关联表数据导出
 * 3. 导出数据的csv header支持自动翻译
 *
 * Created by PhpStorm.
 * User: never615
 * Date: 28/03/2018
 * Time: 8:35 PM
 */
class CsvExporter extends \Encore\Admin\Grid\Exporters\AbstractExporter
{

    use ExporterTrait;

    /**
     * 默认移除的key
     *
     * @var array
     */
    protected $defaultForgetKeys = [
        "images",
        "image",
        "icon",
        "logo",
        "deleted_at",
        "top_subject_id",
        "subject_id",
    ];

    /**
     * 只支持数据库字段是json类型的在此设置.
     *
     *
     * 部分数据以json形式保存在数据库,默认会转成数组,数组的key会当做列名做导出处理,所以在此排除.
     *
     * @var array
     */
    public $ignore2Array = [];


    /**
     * {@inheritdoc}
     */
    public function export()
    {
        if ( ! ini_get('safe_mode')) {
            set_time_limit(60 * 60 * 5);
        }

        $tableName = $this->getTable();

        $fileName = $this->getFileName(".csv");

        $headers = [
            'Content-Encoding'    => 'UTF-8',
            'Content-Type'        => 'text/csv;charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        $response = response()->streamDownload(function () use ($tableName) {
            $handle = fopen('php://output', 'w');

            $titles = [];

            $this->chunkById(function (Collection $records) use (&$titles, $handle, $tableName) {

                if ($records && count($records) > 0) {
                    fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // 添加 BOM

                    //todo 优化,减少多次循环的逻辑
                    $records = $records->map(function (Model $record) {
                        //多维数组转成以小数点连接的以为数据,对应有关联对象的数据需要这样处理
                        //但是有的数据自己本身有json类型的数据字段,需要排除这样处理

                        //todo 代码自动处理这一逻辑,检查record的属性是否是关联对象,如果不是且是数组
                        //todo 则自动加入到ignore2Array中,排除转换

                        return array_dot2($record->toArray(), $this->ignore2Array);
                    });
                    $records = $records->toArray();

                    $records = $this->customData($records);

                    if (empty($titles)) {
                        $titles = $this->getHeaderRowFromRecords($records, $tableName);

                        // Add CSV headers
                        fputcsv($handle, $titles);
                        unset($titles);
                    }

                    foreach ($records as $record) {
                        if ($record) {
                            fputcsv($handle, $this->getFormattedRecord($record));
                        }
                    }
                }
            });

            // Close the output stream
            fclose($handle);
        }, $fileName, $headers);

        if ( ! config("admin.swoole")) {
            $response->send();
            exit();
        } else {
            return $response;
        }
    }


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
        //其他数据处理

        //此方法必须调用
        return $this->forget($records, [
        ]);
    }

}
