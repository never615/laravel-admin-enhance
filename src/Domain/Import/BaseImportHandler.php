<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Domain\Import;

use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Mallto\Admin\Data\ImportRecord;

/**
 * Created by PhpStorm.
 * User: never615 <never615.com>
 * Date: 2018/11/1
 * Time: 下午7:16
 */
abstract class BaseImportHandler
{
    /**
     * 导入任务处理
     *
     * 使用的七牛私有空间的filesystem,可以重写该方法,按自己的需求使用filesystem
     *
     * @param ImportRecord $importRecord
     * @return mixed
     */
    public function handle($importRecord)
    {
        //文件上传到了七牛的私有空间,读取
        $qiniuPrivate = Storage::disk(config("admin.upload.private_disk"));
        $url = $qiniuPrivate->privateDownloadUrl($importRecord->file_url);
        $contents = file_get_contents($url);

        //保存到服务器本地临时目录,便于读取文件
        $tempFileName = $importRecord->subject_id."_".$importRecord->module_slug."_".$importRecord->created_at.".xls";
        Storage::disk("local")->put('tmp/import_file/'.$tempFileName, $contents);
        $path = storage_path("app/tmp/import_file/".$tempFileName);


        Excel::load($path, function (LaravelExcelReader $reader) use ($importRecord) {
            $this->dataHandle($reader, $importRecord);
        });
    }

    /**
     * 处理导入的数据
     * 通过调用 $reader->chunk(500,function(){...},false)  false 表示导入读数据不使用队列任务,因为这个导入任务本身已经在队列任务中了
     *
     * @param LaravelExcelReader $reader
     * @param                    $importRecord
     * @return mixed
     */
    public abstract function dataHandle(LaravelExcelReader $reader, $importRecord);


    /**
     * 导入的队列任务执行失败的时候会触发
     *
     * @param  ImportRecord $record
     * @param null          $exception
     * @return mixed
     */
    public abstract function fail($record, $exception = null);

    /**
     * 修改导入任务状态
     *
     * @param              $importRecord
     * @param ImportRecord::STATUS $status
     * @param null         $finishAt
     * @param null         $failureReason
     */
    protected function updateRecordStatus($importRecord, $status,
        $finishAt = null, $failureReason = null)
    {
        $importRecord->status = $status;
        $importRecord->failure_reason = $failureReason ?: null;
        $importRecord->finish_at = $finishAt ?: null;
        $importRecord->save();
    }

    protected function logError(
        $line,
        $failReason,
        $msg
    ) {
        $failReason .= "第".($line - 1)."行错误:".$msg;

        return $failReason;

    }

}