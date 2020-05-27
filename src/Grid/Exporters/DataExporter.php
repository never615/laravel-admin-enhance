<?php
/**
 * Copyight (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Exporters;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Model;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mallto\Admin\Data\Report;
use Mallto\Tool\Utils\TimeUtils;
use ReflectionClass;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 *
 * 导出报表的关键都在于为了支持大数据导出,
 * 实现了后台报表导出且laravel的orm数据操作又内存泄露.所以使用了\DB中的方法进行操作.由此也就带来了很多麻烦的东西.
 * 有时间可以从底层改造,使用\DB的操作.写报表导出的时候就会方便很多.
 *
 * @deprecated
 * Created by PhpStorm.
 * User: never615
 * Date: 29/03/2017
 * Time: 8:35 PM
 */
abstract class DataExporter extends \Encore\Admin\Grid\Exporters\AbstractExporter implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3600;


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


    /**
     * Create a new job instance.
     *
     * @param      $inputs
     * @param      $subjectId
     * @param      $reportId
     * @param null $adminUserId
     */
    public function __construct($inputs = null, $subjectId = null, $reportId = null, $adminUserId = null)
    {
        $this->inputs = $inputs;
        $this->subjectId = $subjectId;
        $this->report = Report::find($reportId);

        $this->adminUserId = $adminUserId;
    }


    /**
     * Execute the job.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function handle()
    {
        Log::info("执行导出任务");

        $report = $this->report;
        if ($report) {

            $report->update([
                "status" => Report::IN_PROGRESS,
            ]);

            $className = $this->model();
            $class = new ReflectionClass($className); // 建立 Person这个类的反射类
            $instance = $class->newInstance(); // 相当于实例化Person 类
            $tableName = $instance->getTable();
            $this->table = $tableName;
            $model = new Model($instance);
            $filter = new Filter($model);
            $this->filter($filter);
            $this->inputs = ExportUtils::formatInput($tableName, $this->inputs);
            $query = $filter->executeForQuery($this->inputs, $this->subjectId, $this->whereExistCallback());

            $query = ExportUtils::dynamicData($tableName, $this->subjectId, $query, $this->adminUserId);
//            $now = TimeUtils::getNowTime();
//            $fp = fopen(storage_path('exports')."/".admin_translate($tableName)."_".$now."_".substr(time(), 5).".csv", "a");

//            $savePath = storage_path('app/public/exports');
            $savePath = 'public/exports';
            if ( ! Storage::exists($savePath)) {
                Storage::makeDirectory($savePath);
            }

            $fp = fopen(storage_path('app/public/exports') . "/" . $report->name, "a");
            fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF)); // 添加 BOM
            $firstWrite = true;
            $query = $this->customQuery($query, $this->subjectId);
            $query->orderBy($tableName . ".id")->chunk(1000,
                function ($data) use (&$firstWrite, $fp, $tableName) {
                    $data = json_decode(json_encode($data), true);

                    $data = $this->customData($data);
                    //有一些列总是不导出,如icon,image,images
                    $data = ExportUtils::removeInvalids($data);
                    //写列名
                    if ($firstWrite) {
                        $columnNames = [];
                        //获取列名
                        foreach ($data[0] as $key => $value) {
                            $columnNames[] = admin_translate($key, $tableName);
                        }
                        fputcsv($fp, $columnNames);

                        unset($columnNames);
                        $firstWrite = false;
                    }
                    foreach ($data as $item) {
                        fputcsv($fp, $item);
                    }
                });

            fclose($fp);

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
            Log::info("导出失败:report not found");
        }
    }


    /**
     * The job failed to process.
     *
     * @param Exception $e
     */
    public function failed(Exception $e)
    {
        // 发送失败通知, etc...

        Log::error("导出失败");
        Log::error($e);

        $this->report->update([
            "finish" => true,
            "status" => Report::ERROR . $e,
        ]);
    }

    //------------------------------ 以上是导出job ----------------------


    /**
     * {@inheritdoc}
     * @throws \ReflectionException
     */
    public function export()
    {
        $tableName = $this->getTable();
        $now = TimeUtils::getNowTime();
        $fileName = admin_translate("table." . $tableName) . "_" . $now . "_" . substr(time(), 5) . '.csv';

        $adminUser = Admin::user();
        $subjectId = $adminUser->subject_id;

        $inputs = \Request::all();

        $class = new ReflectionClass($this->model()); // 建立 Person这个类的反射类
        $instance = $class->newInstance(); // 相当于实例化Person 类
        $model = new Model($instance);
        $filter = new Filter($model);
        $this->filter($filter);
        $inputs = ExportUtils::formatInput($tableName, $inputs);
        $query = $filter->executeForQuery($inputs, $subjectId, $this->whereExistCallback());
        $query = ExportUtils::dynamicData($tableName, $subjectId, $query, $adminUser->id);
        $count = $query->count();
        if ($count < 30000) {
            $response = new StreamedResponse(null, 200, [
                'Content-Type'        => 'text/csv;charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);
            $response->setCallback(function () use ($query, $tableName, $subjectId) {
                $out = fopen('php://output', 'w');
                fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // 添加 BOM
                $firstWrite = true;
                $query = $this->customQuery($query, $subjectId);
                $query->orderBy($tableName . ".id")->chunk(500,
                    function ($data) use (&$firstWrite, $out, $tableName) {
                        $data = json_decode(json_encode($data), true);
                        $data = $this->customData($data);
                        //有一些列总是不导出,如icon,image,images
                        $data = ExportUtils::removeInvalids($data);
                        //写列名
                        if ($firstWrite) {
                            $columnNames = [];
                            //获取列名
                            foreach ($data[0] as $key => $value) {
                                $columnNames[] = admin_translate($key, $tableName);
                            }
                            fputcsv($out, $columnNames);

                            unset($columnNames);
                            $firstWrite = false;
                        }
                        foreach ($data as $item) {
                            fputcsv($out, $item);
                        }
                    });

                fclose($out);
            });
            $response->send();
            exit;
        } else {
            $tableName = admin_translate("table." . $tableName);
            if (Report::where("finish", false)->where("name", "like", "$tableName%")->exists()) {

                echo <<<EOT
<script type="text/javascript">
alert("该模块有任务正在运行,请稍后再试.");
window.close()
</script>
EOT;
                exit;
            } else {
                $allBigData = $this->allBigData();
                if (empty($allBigData)) {

                    echo <<<EOT
<script type="text/javascript">
alert("该模块暂不支持大量数据导出");
window.close()
</script>
EOT;
                    exit;
                } else {
                    $report = Report::create([
                        "name"          => $fileName,
                        "status"        => Report::NOT_START,
                        "subject_id"    => $subjectId,
                        "admin_user_id" => Admin::user()->id,
                    ]);

                    //todo 看看能不能优化
                    $class = new ReflectionClass(get_class($this));

                    $instance = $class->newInstanceArgs([
                        \Request::all(),
                        $subjectId,
                        $report->id,
                        $adminUser->id,
                    ]); // 相当于实例化Person 类

                    dispatch($instance);

                    echo <<<EOT
<script type="text/javascript">
alert("导出数据量过大,将会进行后台导出,导出进度请到报表管理查看");
location.href="/admin/reports";
</script>
EOT;
                    exit;
                }
            }
        }
    }


    /**
     * 自定义数据处理
     *
     * 这一步就是对即将到放入表格中的数据最后的加工
     *
     * @param array $datas
     *
     * @return mixed
     */
    public abstract function customData($datas);


    /**
     * 查询数据的方法
     *
     * @param      $query
     * @param null $subjectId
     *
     * @return mixed
     */
    public abstract function customQuery($query, $subjectId = null);


    /**
     * 是否允许大数据导出
     *
     * @return mixed
     */
    public abstract function allBigData();


    /**
     * 数据模型
     *
     * @return mixed
     */
    public abstract function model();


    /**
     * 过滤器处理,一般就是列表grid中配置的过滤器,特别的需要单独处理.
     * 需要添加key的表名
     *
     * @param $filter
     *
     * @return mixed
     */
    public abstract function filter($filter);


    /**
     * 存下关联数据查询的时候需要实现先关查询操作
     *
     * 即过滤器中的查询存在关联数据的查询,这个时候相关的查询操作无法自动实现,需要自己实现这个回调.
     *
     * 主要是因为底层的查询等操作都是orm的.所以想好好改进这个问题的话,需要重写.
     *
     * @return \Closure
     */
    public function whereExistCallback()
    {
        return function () {
            $tempArgs = func_get_args();
            $dbQuery = $tempArgs[0];

            return $dbQuery;
        };
    }

}
