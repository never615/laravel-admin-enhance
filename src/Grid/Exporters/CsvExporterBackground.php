<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Exporters;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mallto\Admin\Data\Report;

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
class CsvExporterBackground extends \Encore\Admin\Grid\Exporters\AbstractExporter
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
     * 是否在队列任务中运行
     */
    public function isRunInQueue()
    {
        return request()->header('mode') === 'queue';
    }


    /**
     * {@inheritdoc}
     */
    public function export()
    {
        //删除排序
        request()->query->remove('_sort');

        $tableName = $this->getTable();

        $fileName = $this->getFileName(".csv");

        //区分是队列任务还是直接导出
        if ($this->isRunInQueue()) {
            //后台导出

            //\Log::debug('后台导出');

            $this->backgroundExport($tableName, $fileName);

            return;
        }

        if ( ! ini_get('safe_mode')) {
            set_time_limit(60 * 60 * 24);
            ini_set("memory_limit", "512M");
        }

        $count = $this->getQuery()->count();

        $controllerClass = null;
        $array = debug_backtrace();
        unset($array[0]);
        foreach ($array as $row) {
            if (isset($row['args'][0]) && $row['args'][0] instanceof Route) {
                //\Log::debug($row['args'][0]);
                //\Log::debug('route');

                $route = $row['args'][0];
                //$route=new Route();
                $controllerClass = get_class($route->controller);
                //\Log::debug($controllerClass);
            }

            //\Log::debug($row['file'] . ':' . $row['line'] . '行,调用方法:' . $row['function']);
        }

        if ($count <= 2000) {
            $headers = [
                'Content-Encoding'    => 'UTF-8',
                'Content-Type'        => 'text/csv;charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"$fileName\"",
            ];

            $response = response()->streamDownload(function () use ($tableName) {
                $handle = fopen('php://output', 'w');

                $this->chunkForWrite($handle, $tableName);

            }, $fileName, $headers);

            if ( ! config("admin.swoole")) {
                $response->send();
                exit();
            } else {
                return $response;
            }
        } else {
            //导出数量过大,使用后台导出,然后在报表中心进行下载
            return $this->alertForExporterJob($controllerClass, $tableName, $fileName);
        }
    }


    /**
     * 后台导出逻辑
     *
     * @param $tableName
     * @param $fileName
     */
    public function backgroundExport($tableName, $fileName)
    {
        $reportId = request()->header('report');

        //调用导出代码的的导出类
        //$exporterClass = new ReflectionClass($exporterClassName);
        //$exporterInstance = $exporterClass->newInstanceArgs([ $grid ]); // 相当于实例化Person 类

        //写文件
        $savePath = 'public/exports';
        if ( ! Storage::exists($savePath)) {
            Storage::makeDirectory($savePath);
        }

        $report = Report::query()->find($reportId);

        $handle = fopen(storage_path('app/public/exports') . "/" . $report->name, "a");

        $this->chunkForWrite($handle, $tableName, $report);

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
    }


    /**
     * 后台导出对话框交互逻辑
     *
     * @param $controllerClass
     * @param $tableName
     * @param $fileName
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function alertForExporterJob($controllerClass, $tableName, $fileName)
    {
        $adminUser = Admin::user();
        $subjectId = $adminUser->subject_id;

        if (Report::where("finish", false)->where("table_name", $tableName)->exists()) {

            echo <<<EOT
<script type="text/javascript">
alert("该模块有导出任务正在运行,请稍后再试.");
location.href="/admin/reports";
// window.close()
</script>w
EOT;
            if ( ! config("admin.swoole")) {
                exit();
            } else {
                return redirect('/admin/reports');
            }
        } else {
            $report = Report::create([
                "name"          => $fileName,
                'table_name'    => $tableName,
                "status"        => Report::NOT_START,
                "subject_id"    => $subjectId,
                "admin_user_id" => $adminUser->id,
            ]);

            $job = new ExporterJob(
                $controllerClass,
                request()->all(),
                //$report->id,
                $report,
                $adminUser->id
            );

            dispatch($job);

            echo <<<EOT
<script type="text/javascript">
alert("导出数据量过大,将会进行后台导出,导出进度请到报表管理查看");
location.href="/admin/reports";
</script>
EOT;

            if ( ! config("admin.swoole")) {
                exit();
            } else {
                //throw new ResourceException('111');
                return redirect('/admin/reports');
            }
        }
    }


    /**
     * chunk 写入逻辑
     *
     * @param $handle
     * @param $tableName
     * @param $report
     */
    public function chunkForWrite($handle, $tableName, $report = null)
    {
        fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // 添加 BOM

        $titles = [];
        $this->chunkById(function (Collection $records) use (
            &$titles,
            $handle,
            $tableName,
            $report
        ) {
            //\Log::debug($records);
            if ($records && count($records) > 0) {
                //fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // 添加 BOM

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

                if ( ! empty($report)) {
                    try {
                        $nowReport = $report->refresh();
                    } catch (ModelNotFoundException $modelNotFoundException) {
                        fclose($handle);

                        return;
                    }
                    if ( ! $nowReport->export_total) {
                        $nowReport->export_total = $this->getQuery()->count('id');
                    }

                    if ( ! $nowReport->now_total) {
                        $nowReport->now_total = 200;
                    } else {
                        $nowReport->now_total += 200;
                    }

                    $nowReport->now_percentage = round($nowReport->now_total / $nowReport->export_total, 2);

                    $nowReport->save();
                }
            }

        }, 200);

        // Close the output stream
        fclose($handle);
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
