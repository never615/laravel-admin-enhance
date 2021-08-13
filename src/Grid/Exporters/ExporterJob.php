<?php
/**
 * Copyright (c) 2021. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Exporters;

use Encore\Admin\Facades\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Mallto\Admin\Data\Administrator;
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
    public $timeout = 3600 * 36;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    protected $inputs;

    protected $table;

    protected $report;

    /**
     * @var null
     */
    private $adminUserId;

    private $controllerClass;


    /**
     * Create a new job instance.
     *
     * @param      $controllerClass
     * @param      $inputs
     * @param      $reportId
     * @param null $adminUserId
     */
    public function __construct(
        $controllerClass,
        $inputs,
        //$reportId,
        $report,
        $adminUserId
    ) {
        $this->inputs = $inputs;
        //$this->report = Report::find($reportId);
        $this->report = $report;
        $this->adminUserId = $adminUserId;
        $this->controllerClass = $controllerClass;
    }


    public function handle()
    {
        $report = $this->report;
        $controllerClassName = $this->controllerClass;

        //\Log::debug($exporterClassName);
        //\Log::debug($controllerClassName);

        if ($report) {
            $report->update([
                "status" => Report::IN_PROGRESS,
            ]);

            //hack request
            request()->merge($this->inputs);

            request()->headers->set("mode", "queue");
            request()->headers->set("report", $report->id);

            //\Log::debug(request()->all());

            //调用导出代码的controller
            $controllerClass = new ReflectionClass($controllerClassName); // 建立 Person这个类的反射类
            $controllerInstance = app($controllerClassName);

            ////拿到当前导出类的构造函数
            //$constructor = $controllerClass->getConstructor();
            //
            ////如果没有构造函数，正常走
            //if (is_null($constructor)) {
            //    //相当于实例化Person 类
            //    $controllerInstance = $controllerClass->newInstance();
            //} else {
            //    //构造函数依赖的参数
            //    $dependencies = $constructor->getParameters();
            //    //根据参数返回实例
            //    $instances = $this->getDependencies($dependencies);
            //    //传参并实例化
            //    $controllerInstance = $controllerClass->newInstanceArgs($instances);
            //}

            $adminUser = Administrator::query()->find($this->adminUserId);
            //\Log::debug($adminUser);
            Admin::setUser($adminUser);

            $controllerInstance->adminUser = $adminUser;

            //$indexMethod = $controllerClass->getMethod('index'); // 得到ReflectionMethod对象
            //$content = new Content();
            //$indexMethod->invoke($controllerInstance, [ $content ]);// 传入对象来访问这个方法

            $gridMethod = $controllerClass->getMethod('grid'); // 得到ReflectionMethod对象
            $gridMethod->setAccessible(true);// 设置为可见，也就是可访问
            $grid = $gridMethod->invoke($controllerInstance);// 传入对象来访问这个方法
            //\Log::debug(333);
            $grid->handleExportRequest(true);

            return;
        } else {
            Log::warning("导出失败:report not found",
                [ $this->reportId, $this->inputs, $this->subjectId, $this->adminUserId ]);
        }
    }


    /**
     * The job failed to process.
     *
     * @param \Exception $e
     */
    public function failed(\Exception $e)
    {
        Log::error("导出失败");
        Log::warning($e);

        $this->report->update([
            "finish" => true,
            "status" => Report::ERROR,
        ]);
    }


    /**
     * 解析构造函数
     *
     * @param $params
     *
     * @return array
     */
    public function getDependencies($params)
    {
        $dependencies = [];
        $container = new Container();

        foreach ($params as $param) {
            $dependencies[] = $container->make($param->getClass()->name);
        }

        return $dependencies;
    }

}
