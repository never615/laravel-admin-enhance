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
            $setting = ImportSetting::where("module_slug", $record->module_slug)
                ->first();
            if ($setting) {
                $handler = resolve($setting->module_handler);
                $handler->handle($record);
            } else {
                $handler = resolve($record->module_slug);
                if ($handler) {
                    $handler->handle($record);
                }
            }
        }
    }


    public function failed($exception)
    {
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
