<?php
/**
 * Copyright (c) 2021. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Exporters;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mallto\Admin\Data\Report;
use ReflectionClass;

/**
 * User: never615 <never615.com>
 * Date: 2021/1/13
 * Time: 2:26 上午
 */
class ExporterJob implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3600 * 12;

    /**
     * @var
     */
    protected $subjectId;

    protected $inputs;

    protected $table;

    protected $report;

    /**
     * @var null
     */
    private $adminUserId;

    private $reportId;


    private $tableName;

    private $exporterClass;

    private $controllerClass;


    /**
     * Create a new job instance.
     *
     * @param      $controllerClass
     * @param      $exporterClass
     * @param      $tableName
     * @param      $model
     * @param      $inputs
     * @param      $subjectId
     * @param      $reportId
     * @param null $adminUserId
     */
    public function __construct(
        $controllerClass,
        $exporterClass,
        $tableName,
        $inputs,
        $subjectId,
        $reportId,
        $adminUserId = null
    ) {
        $this->inputs = $inputs;
        $this->subjectId = $subjectId;
        $this->report = Report::find($reportId);
        $this->adminUserId = $adminUserId;
        $this->reportId = $reportId;
        $this->tableName = $tableName;
        $this->exporterClass = $exporterClass;
        $this->controllerClass = $controllerClass;
    }


    public function handle()
    {
        $report = $this->report;
        $tableName = $this->tableName;
        $controllerClassName = $this->controllerClass;
        $exporterClassName = $this->exporterClass;

        //\Log::debug($exporterClassName);
        //\Log::debug($controllerClassName);

        //hack request
        request()->merge($this->inputs);

        if ($report) {
            $report->update([
                "status" => Report::IN_PROGRESS,
            ]);

            //\Log::debug(request()->all());

            //调用导出代码的controller
            $controllerClass = new ReflectionClass($controllerClassName); // 建立 Person这个类的反射类
            $controllerInstance = $controllerClass->newInstance(); // 相当于实例化Person 类

            //$indexMethod = $controllerClass->getMethod('index'); // 得到ReflectionMethod对象
            //$content = new Content();
            //$indexMethod->invoke($controllerInstance, [ $content ]);// 传入对象来访问这个方法

            $gridMethod = $controllerClass->getMethod('grid'); // 得到ReflectionMethod对象
            $gridMethod->setAccessible(true);// 设置为可见，也就是可访问
            $grid = $gridMethod->invoke($controllerInstance);// 传入对象来访问这个方法

            $grid->handleExportRequest(true);

            //$grid = new Grid($modelInstance);

            //调用导出代码的的导出类
            $exporterClass = new ReflectionClass($exporterClassName);
            $exporterInstance = $exporterClass->newInstanceArgs([ $grid ]); // 相当于实例化Person 类

            $grid->applyQuery();

            //写文件
            $savePath = 'public/exports';
            if ( ! Storage::exists($savePath)) {
                //\Log::debug($savePath);
                Storage::makeDirectory($savePath);
            }

            $handle = fopen(storage_path('app/public/exports') . "/" . $report->name, "a");
            fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // 添加 BOM
            $titles = [];

            $firstWrite = true;

            $exporterInstance
                ->chunk(function (Collection $records) use (
                    &$titles,
                    &$firstWrite,
                    $handle,
                    $tableName,
                    $exporterInstance
                ) {
                    //\Log::debug($records);
                    if ($records && count($records) > 0) {
                        //fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // 添加 BOM

                        //todo 优化,减少多次循环的逻辑
                        $records = $records->map(function (Model $record) use ($exporterInstance) {
                            //多维数组转成以小数点连接的以为数据,对应有关联对象的数据需要这样处理
                            //但是有的数据自己本身有json类型的数据字段,需要排除这样处理

                            //todo 代码自动处理这一逻辑,检查record的属性是否是关联对象,如果不是且是数组
                            //todo 则自动加入到ignore2Array中,排除转换

                            return array_dot2($record->toArray(), $exporterInstance->ignore2Array);
                        });
                        $records = $records->toArray();

                        $records = $exporterInstance->customData($records);

                        if (empty($titles)) {
                            $titles = $exporterInstance->getHeaderRowFromRecords($records, $tableName);

                            // Add CSV headers
                            fputcsv($handle, $titles);
                            unset($titles);
                        }

                        foreach ($records as $record) {
                            if ($record) {
                                fputcsv($handle, $exporterInstance->getFormattedRecord($record));
                            }
                        }
                    }

                }, 200);

            fclose($handle);

            //上传文件到七牛
            $disk = Storage::disk("qiniu_private");

            $filePath = public_path('storage/exports/' . $report->name);
//            \Log::info($filePath);

            $disk->put(config("app.unique") . '/' . config("app.env") . '/exports/' . $report->name,
                fopen($filePath, 'r+')); //分段上传文件。建议大文件>10Mb使用。

            $report->update([
                "finish" => true,
                "status" => Report::FINISH,
            ]);

            Log::info("导出完成");
        } else {
            Log::warning("导出失败:report not found",
                [ $this->reportId, $this->inputs, $this->subjectId, $this->adminUserId ]);
        }
    }

}
