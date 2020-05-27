<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Domain\Import;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Exceptions\RowSkippedException;
use Maatwebsite\Excel\Validators\RowValidator;
use Throwable;

/**
 *
 * 使用参考: https://docs.laravel-excel.com/3.1/imports/collection.html
 *
 * User: never615 <never615.com>
 * Date: 2019/11/26
 * Time: 7:52 下午
 */
class ArrayBaseImport extends BaseImport implements ToArray
{

    /**
     * @var RowValidator
     */
    private $validator;


    /**
     * ArrayBaseImport constructor.
     *
     * @param                   $importRecord
     * @param BaseImportHandler $importHandler
     */
    public function __construct(
        $importRecord,
        BaseImportHandler $importHandler
    ) {
        $this->validator = app(RowValidator::class);
        parent::__construct($importRecord, $importHandler);
    }


    /**
     * @param array $rows
     *
     * @throws \Maatwebsite\Excel\Validators\ValidationException
     */
    public function array(array $rows)
    {
        try {
            $this->validator->validate($rows, $this);
        } catch (RowSkippedException $e) {
            foreach ($e->skippedRows() as $row) {
                unset($rows[$row]);
            }
        }

        try {
            $this->importHandler->dataHandler($this->importRecord, $rows);
        } catch (Throwable $e) {
            $this->onError($e);
        }
    }

}
