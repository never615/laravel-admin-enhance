<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Domain\Import;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;

/**
 * User: never615 <never615.com>
 * Date: 2019/11/26
 * Time: 7:52 下午
 */
class BaseImport implements WithChunkReading, WithValidation,
                            WithHeadingRow, WithEvents, SkipsOnError, SkipsOnFailure
{

    use Importable, SkipsFailures, SkipsErrors;

    /**
     * @var BaseImportHandler
     */
    public $importHandler;

    public $importRecord;


    /**
     * BaseImport constructor.
     *
     * @param                   $importRecord
     * @param BaseImportHandler $importHandler
     */
    public function __construct(
        $importRecord,
        BaseImportHandler $importHandler
    ) {
        $this->importHandler = $importHandler;
        $this->importRecord = $importRecord;
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
