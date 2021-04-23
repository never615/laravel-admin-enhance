<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

/**
 * Copyight (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Exporters;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

/**
 *
 * 导出报表excel
 *
 * Created by PhpStorm.
 * User: never615
 * Date: 28/03/2018
 * Time: 8:35 PM
 */
abstract class ExcelExporter extends \Encore\Admin\Grid\Exporters\AbstractExporter
{

    use ExporterTrait;


    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $tableName = $this->getTable();
        $fileName = $this->getFileName();

        Excel::create($fileName, function ($excel) use ($tableName) {

            $excel->sheet('sheet1', function ($sheet) use ($tableName) {
                $sheet->setAutoSize(true);
                $this->chunkById(function (Collection $records) use (&$titles, $tableName, $sheet) {
                    $records = $this->customData($records);

                    if (empty($titles)) {
                        $titles = $this->getHeaderRowFromRecords($records, $tableName);
                        $sheet->appendRow($titles);
                        unset($titles);
                    }

                    foreach ($records as $record) {
                        if ($record) {
                            $sheet->appendRow($this->getFormattedRecord($record));
                        }
                    }
                });
            });

        })->export('xls');

        exit;
    }

}
