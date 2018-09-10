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

    //只支持设置为json
    protected $ignore2Array = [];

    /**
     * {@inheritdoc}
     */
    public function export()
    {

        if (!ini_get('safe_mode')) {
            set_time_limit(3600);
        }


        $tableName = $this->getTable();

        $fileName = $this->getFileName(".csv");


        $headers = [
            'Content-Encoding'    => 'UTF-8',
            'Content-Type'        => 'text/csv;charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        response()->stream(function () use ($tableName) {
            $handle = fopen('php://output', 'w');

            $titles = [];

            $this->chunk(function (Collection $records) use (&$titles, $handle, $tableName) {
                if ($records && count($records) > 0) {
                    fwrite($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // 添加 BOM


                    $records = $records->map(function (Model $record) {
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
                        fputcsv($handle, $this->getFormattedRecord($record));
                    }
                }
            });

            // Close the output stream
            fclose($handle);
        }, 200, $headers)->send();

        exit;
    }


}
