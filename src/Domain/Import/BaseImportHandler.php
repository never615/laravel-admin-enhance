<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Domain\Import;

use ErrorException;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Mallto\Admin\Data\ImportRecord;
use Mallto\Tool\Utils\TimeUtils;

/**
 *
 * 使用laravel-excel处理导入
 *
 * @link https://docs.laravel-excel.com/3.1/imports/validation.html
 *
 * Created by PhpStorm.
 * User: never615 <never615.com>
 * Date: 2018/11/1
 * Time: 下午7:16
 */
abstract class BaseImportHandler
{

    /**
     * 导入模式,可选: array and models
     *
     * 设置不同的模式,则public function dataHandler($importRecord, $rows) 的$rows参数返回不同的数据.
     *
     *
     * models:  $rows返回一条数据(array),即导入的一行数据.参考https://docs.laravel-excel.com/3.1/imports/model.html
     * 中的model()方法,需要返回model.
     *
     * eachRow:
     * $rows返回一条数据(array),即导入的一行数据.参考:https://docs.laravel-excel.com/3.1/imports/model.html#handling-persistence-on-your-own
     *
     * array:
     * $rows返回导入的数据的二维数组,数组中每一条子数组表示导入的一行数据.使用参考:https://docs.laravel-excel.com/3.1/imports/collection.html
     *
     * @return mixed
     */
    public $importMode = 'models';


    /**
     * BaseImportHandler constructor.
     */
    public function __construct()
    {
        HeadingRowFormatter::default('none');
    }


    /**
     * collections and models
     *
     * @link https://docs.laravel-excel.com/3.1/imports/collection.html
     * @return mixed
     */
    public function getImportMode()
    {
        return $this->importMode;
    }


    /**
     * 导入任务处理
     *
     * 使用的七牛私有空间的filesystem,可以重写该方法,按自己的需求使用filesystem
     *
     * @param ImportRecord $importRecord
     *
     * @return mixed
     */
    public function handle($importRecord)
    {
        //文件上传到了七牛的私有空间,读取
        $qiniuPrivate = Storage::disk(config('admin.upload.private_disk'));

        $url = $qiniuPrivate->privateDownloadUrl($importRecord->file_url);

        $fileUrls = explode('.', $importRecord->file_url);
        $ext = array_last($fileUrls);

        try {
            $contents = file_get_contents($url);
        } catch (ErrorException $errorException) {
            $this->updateRecordStatus($importRecord, 'failure',
                '文件名不能包含特殊字符,只能是字母/数字/-_');
        }

        $moduleSlug = $importRecord->module_slug;
        if (str_contains($moduleSlug, '\\')) {
            $moduleSlug = array_last(explode('\\', $moduleSlug));
        }
        //保存到服务器本地临时目录,便于读取文件
        $tempFileName = $importRecord->subject_id . '_' . $moduleSlug . '_' . $importRecord->created_at . '.' . $ext;
        Storage::disk('local')->put('tmp/import_file/' . $tempFileName, $contents);
        $path = storage_path('app/tmp/import_file/' . $tempFileName);

        //1. 修改状态为进行中
        $this->updateRecordStatus($importRecord, 'processing');

        //2. 校验导入列名
        $importKeys = (new HeadingRowImport)->toArray($path);
        $importKeys = $importKeys[0][0];

        //\Log::debug($importKeys);
        //foreach ($importKeys as $key => $importKey) {
        //if ($importKey) {
        //    $this->updateRecordStatus($importRecord, 'failure', '列中有空列名,请对照导入模板检查');
        //}
        //}

        $expectKeys = $this->getExpectKeys();

        if (array_diff($expectKeys, $importKeys) || array_diff($importKeys, $expectKeys)) {
            $this->updateRecordStatus($importRecord, 'failure', '列名错误,请对照导入模板检查');
            \Log::warning($expectKeys);
            \Log::warning($importKeys);

            return false;
        }

        //3. 开始导入
        switch ($this->getImportMode()) {
            case 'models':
                $import = (new ModelBaseImport($importRecord, $this));
                break;
            case 'array':
                $import = (new ArrayBaseImport($importRecord, $this));
                break;
            case 'eachRow':
                $import = (new EachRowBaseImport($importRecord, $this));
                break;
            default:
                $this->updateRecordStatus($importRecord, 'failure',
                    '导入模式配置错误'
                );

                return false;
        }

        $import->import($path);

        $failures = $import->failures();
        $errors = $import->errors();

        if (count($failures) === 0 && count($errors) === 0) {
            $this->updateRecordStatus($importRecord, 'success');
        } else {
            $errorMsg = '';

            foreach ($failures as $failure) {
                $line = $failure->row(); // row that went wrong
//                $key = $failure->attribute(); // either heading key (if using heading row concern) or column index
                $failureErrors = $failure->errors(); // Actual error messages from Laravel validator
//                $values = $failure->values(); // The values of the row that has failed.

                $tempMsg = implode('/', $failureErrors);

                //$tempLine = $line + 1;

                $errorMsg .= "第$line 行,$tempMsg \n";
            }

            foreach ($errors as $error) {
                $tempMsg = $error->getMessage();

                $errorMsg .= "$tempMsg \n";
            }

            $this->updateRecordStatus($importRecord, 'partially_failure',
                $errorMsg
            );
        }
    }


    /**
     * 获取导入文件期望的列名
     *
     * @return mixed
     */
    abstract public function getExpectKeys();


    /**
     * 导入验证规则
     *
     * @link https://docs.laravel-excel.com/3.1/imports/validation.html
     *
     * @param $importRecord
     *
     * @return array
     */
    abstract public function rule($importRecord);


    /**
     * 插入数据
     *
     * @param                  $importRecord
     * @param array            $row |$rows
     *
     * @return mixed
     * @throws \Exception
     */
    abstract public function dataHandler($importRecord, $row);


    /**
     * 导入之前触发
     *
     * @param $importRecord
     *
     * @return mixed
     */
    abstract public function beforeSheet($importRecord);


    /**
     * 导入之后触发
     *
     * @param $importRecord
     *
     * @return mixed
     */
    abstract public function afterSheet($importRecord);


    /**
     * 导入的队列任务执行失败的时候会触发
     *
     * @param ImportRecord $record
     * @param \Throwable   $exception
     *
     * @return mixed
     */
    public
    function fail(
        $record,
        $exception = null
    ) {
        $this->updateRecordStatus($record, 'failure',
            $exception->getMessage());
    }


    /**
     * 修改导入任务状态
     *
     * @param              $importRecord
     * @param ImportRecord::STATUS $status
     * @param null         $finishAt
     * @param null         $failureReason
     */
    protected function updateRecordStatus(
        $importRecord,
        $status,
        $failureReason = null,
        $finishAt = null
    ) {
        if ( ! $finishAt && $status !== 'processing') {
            $finishAt = TimeUtils::getNowTime();
        }

        //获取最新的 importRecord
        $importRecord->refresh();

        $msg = $importRecord->failure_reason ? $importRecord->failure_reason . "\n" : '';

        $failureReason = $failureReason ? $msg . $failureReason : $msg;

        if ($failureReason && $status === 'success') {
            $status = 'partially_failure';
        }

        $importRecord->status = $status;
        $importRecord->failure_reason = $failureReason;
        $importRecord->finish_at = $finishAt ?: null;
        $importRecord->save();
    }


    /**
     * 默认的rule规则
     *
     * @return array
     */
    public function defaultRule()
    {
        $rule = [];
        foreach ($this->getExpectKeys() as $key) {
            $rule[$key] = 'required';
        }

        return $rule;
    }


    /**
     * @param        $msg
     * @param string $failReason
     * @param null   $line
     *
     * @return string
     * @deprecated
     */
    protected function logError(
        $msg,
        $failReason = '',
        $line = null
    ) {
        if ($line) {
            $failReason .= '第' . ($line - 1) . '行错误:' . $msg;
        } else {
            $failReason .= $msg;
        }

        return $failReason;

    }

}
