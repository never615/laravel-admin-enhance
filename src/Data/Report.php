<?php
/**
 * Copyight (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Model;
use Mallto\Admin\Data\Traits\DynamicData;

class Report extends Model
{

    use DynamicData;

    const NOT_START = "任务未开始";
    const IN_PROGRESS = "任务进行中";
    const FINISH = "已完成";
    const ERROR = "任务失败";

//    protected $fillable = [
//        'id',
//        'name',
//        "status",
//        "subject_id",
//        "admin_user_id",
//        "finish",
//    ];

    protected $guarded = [];


    public function adminUser()
    {
        return $this->belongsTo(Administrator::class, "admin_user_id");
    }


    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }


//    public function getFinishAttribute($finish)
//    {
//        return CastUtils::castBool2YesOrNo($finish);
//    }

}


