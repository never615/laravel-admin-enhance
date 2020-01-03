<?php
/**
 * Copyight (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Exporters;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Support\Facades\Schema;
use Mallto\Admin\Data\Subject;

/**
 * 辅助导出功能使用的工具
 *
 * Created by PhpStorm.
 * User: never615
 * Date: 29/03/2017
 * Time: 7:48 PM
 */
class ExportUtils
{

    public static function dynamicData($tableName, $subjectId, $query, $adminUserId = null)
    {
        if (Schema::hasColumn($tableName, 'subject_id')) {
            if ( ! empty($adminUserId)) {
                //如果设置了manager_subject_ids,则优先处理该值

                $adminUser = Administrator::find($adminUserId);

                $managerSubjectIds = $adminUser->manager_subject_ids;

                if ( ! empty($managerSubjectIds)) {
                    $tempSubject = new Subject();
                    $tempSubjectIds = $managerSubjectIds;

                    foreach ($managerSubjectIds as $managerSubjectId) {
                        $tempSubjectIds = array_merge($tempSubjectIds,
                            $tempSubject->getChildrenSubject($managerSubjectId));
                    }
                    $tempSubjectIds = array_unique($tempSubjectIds);
                } else {
                    $currentSubject = $adminUser->subject;
                    $tempSubjectIds = $currentSubject->getChildrenSubject();
                }
            } else {
                //1.获取当前登录账户属于哪一个主体
                $currentSubject = Subject::find($subjectId);
                //2.获取当前主体的所有子主体
                $ids = $currentSubject->getChildrenSubject($currentSubject->id);
                $tempSubjectIds = $ids;
            }

            //3.限定查询范围为所有子主体
            $query = $query->whereIn($tableName . '.subject_id', $tempSubjectIds);
        }

        return $query;
    }


    public static function formatInput($tableName, $inputs)
    {
        foreach ($inputs as $key => $input) {
            if (strpos($key, "_") != 0) {
                $inputs[$tableName . "." . $key] = $input;
                unset($inputs[$key]);
            }
        }

        return $inputs;
    }

}
