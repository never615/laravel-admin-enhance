<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mallto\Admin\Data\ImportRecord;
use Mallto\Admin\Data\ImportSetting;
use Mallto\Tool\Exception\ResourceException;
use Illuminate\Support\Facades\Log;

class ImportFileJob implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3600 * 3;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * @var
     */
    private $id;


    /**
     * Create a new job instance.
     *
     * @param $id
     */
    public function __construct($id)
    {

        $this->id = $id;
    }


    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        $record = ImportRecord::find($this->id);

        if ($record && $record->status == "not_start") {
            $setting = ImportSetting::where("import_handler", $record->import_handler)
                ->first();
            if ($setting) {
                $handler = resolve($setting->import_handler);
                $handler->handle($record);
            } else {
                Log::error("没有找见对应的导入配置,禁止导入:" . $record->import_handler);
                throw new ResourceException("没有找见对应的导入配置,禁止导入:" . $record->import_handler);
                //$handler = resolve($record->import_handler);
                //if ($handler) {
                //    $handler->handle($record);
                //}
            }
        }
    }


    public function failed($exception)
    {
        Log::warning('导入任务失败');
        Log::warning($exception);
        $record = ImportRecord::find($this->id);
        if ($record && $record->status == "processing") {
            $setting = ImportSetting::where("module_slug", $record->module_slug)
                ->first();
            if ($setting) {
                $handler = resolve($setting->module_handler);
                $handler->fail($record, $exception);
            } else {
                $handler = resolve($record->module_slug);
                if ($handler) {
                    $handler->handle($record);
                }
            }
        }
    }

}
