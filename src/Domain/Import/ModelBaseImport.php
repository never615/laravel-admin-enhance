<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Domain\Import;

use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;

/**
 *
 * 使用参考: https://docs.laravel-excel.com/3.1/imports/model.html
 *
 * User: never615 <never615.com>
 * Date: 2019/11/26
 * Time: 7:52 下午
 */
class ModelBaseImport extends BaseImport implements
    ToModel, WithBatchInserts
{

    /**
     * @param array $row
     *
     * @return Model|Model[]|null
     */
    public function model(array $row)
    {
        return $this->importHandler->dataHandler($this->importRecord, $row);
    }


    /**
     * @return array
     */
    public function rules(): array
    {
        return $this->importHandler->rule($this->importRecord);
    }


    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            // Handle by a closure.
            BeforeSheet::class => function (BeforeSheet $event) {
                $this->importHandler->beforeSheet($this->importRecord);
            },
            AfterSheet::class  => function (AfterSheet $event) {
                $this->importHandler->afterSheet($this->importRecord);
            },
        ];
    }


    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000;
    }


    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 1000;
    }

}
