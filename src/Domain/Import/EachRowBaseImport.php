<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Domain\Import;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Validators\Failure;
use Throwable;

/**
 * 使用参考: https://docs.laravel-excel.com/3.1/imports/model.html#handling-persistence-on-your-own
 *
 * When using OnEachRow you cannot use batch inserts, as the the model is already persisted in the onRow
 * method.
 *
 * User: never615 <never615.com>
 * Date: 2019/11/26
 * Time: 7:52 下午
 */
class EachRowBaseImport extends BaseImport implements
    OnEachRow, WithBatchInserts
{

    /**
     * @param Row $row
     */
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();

        $row = $row->toArray();

        try {
            Validator::make($row, $this->rules())
                ->validate();
        } catch (ValidationException $e) {
            $failures = [];
            foreach ($e->errors() as $attribute => $messages) {

                $failures[] = new Failure(
                    ($rowIndex - 1),
                    $attribute,
                    $messages,
                    $row
                );
            }

            $this->onFailure(...$failures);

            return false;
        }

        try {
            $this->importHandler->dataHandler($this->importRecord, $row);
        } catch (Throwable $e) {
            $e = new $e("第$rowIndex 行错误:" . $e->getMessage(), $e->getCode(), $e->getPrevious());
            $this->onError($e);
        }
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
