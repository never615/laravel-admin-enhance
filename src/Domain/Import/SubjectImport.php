<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Domain\Import;

use Illuminate\Validation\Rule;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\Listeners\Events\SubjectSaved;

class  SubjectImport extends BaseImportHandler
{

    /**
     * 获取导入文件期望的列名
     *
     * @return mixed
     */
    public function getExpectKeys()
    {
        return [
            '主体名称',
            '上级主体名称',
        ];
    }


    /**
     * 插入数据
     *
     * @param      $importRecord
     * @param      $newRow
     *
     * @return mixed
     */
    public function dataHandler($importRecord, $newRow)
    {
        $parentSubject = Subject::query()->where('name', $newRow['上级主体名称'])->first();
        if (!$parentSubject->path) {
            $path = "." . $parentSubject->id . ".";
        } else {
            $path = $parentSubject->path . $parentSubject->id . ".";
        }
        $newSubjcet = Subject::query()->create([
            'parent_id' => $parentSubject->id,
            'name'      => $newRow['主体名称'],
            'path'      => $path,
        ]);
        event(new SubjectSaved($newSubjcet->id));
    }


    /**
     * 导入验证规则
     *
     * @link https://docs.laravel-excel.com/3.1/imports/validation.html
     *
     * @param $importRecord
     *
     * @return array
     */
    public function rule($importRecord)
    {
        return [
            '主体名称'   => [
                'required',
            ],
            '上级主体名称' => [
                'required',
                Rule::exists('subjects', 'name'),
            ],
        ];
    }


    /**
     * 导入之前触发
     *
     * @param $importRecord
     *
     * @return mixed
     */
    public function beforeSheet($importRecord)
    {

    }


    /**
     * 导入之后触发
     *
     * @param $importRecord
     *
     * @return mixed
     */
    public function afterSheet($importRecord)
    {


    }
}
