<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mallto\Admin\Data\Subject;

class SubjectPathUpdateJob implements ShouldQueue
{

//    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use Dispatchable, InteractsWithQueue, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3600;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    public $delay = 5;

    /**
     * @var
     */
    private $subject;


    /**
     * Create a new job instance.
     *
     * @param $id
     */
    public function __construct($subject)
    {
        $this->subject = $subject;
    }


    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        //更新所有子级
        $subjectId = $this->subject->id;
        $subject = Subject::query()->find($subjectId);
        $childrenSubject = $subject->getChildrenSubject($subjectId);
        foreach ($childrenSubject as $childrenSubjectId) {
            $path = $subject->path . $subjectId . '.';
            if ($childrenSubjectId != $subjectId) {
                $subordinateSubject = Subject::query()->find($childrenSubjectId);
                //判断是否是下一级,如果是直接更新,如果不是,拼接path
                if ($subordinateSubject->parent_id != $subjectId) {
                    //查询上级,根据上级的path拼接
                    $superiorSubject = Subject::query()->find($subordinateSubject->parent_id);
                    $path = $superiorSubject->path . $subordinateSubject->parent_id . '.';
                }

                $subordinateSubject->path = $path;
                $subordinateSubject->save();
            }
        }

    }

    /**
     * The job failed to process.
     *
     * @param \Exception $e
     */
    public function failed(\Exception $e)
    {
        \Log::error('同步更新子级path失败');
        \Log::warning($e);
    }
}
