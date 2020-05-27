<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Mallto\Admin\SubjectUtils;
use Request;

/**
 * Created by PhpStorm.
 * User: never615
 * Date: 21/04/2017
 * Time: 5:13 PM
 */
abstract class BaseModel2 extends Model
{

    use DynamicData, SelectSource;

    protected $hidden = [ 'deleted_at' ];

    protected $guarded = [];


    /**
     * 重载save方法,
     * 管理端编辑的对象不能使用此配置,
     * 因为管理端的saving方法可能会使用当前编辑对象的subject_id设置值.
     * 而form->saving方法是在调用下面方法之前调用的
     *
     * @desc 新建对象时自动加subject_id
     *
     * @param array $options
     *
     * @return bool
     */
    public function save(array $options = [])
    {
        if (Request::header("mode") == "api") {
            $tableHasSubjectId = Schema::hasColumn($this->getTable(), 'subject_id');
            $attrsHasSubjectId = empty($this->attributes['subject_id']);
            if ($tableHasSubjectId && ! $this->exists && $attrsHasSubjectId) {
                $this->attributes['subject_id'] = SubjectUtils::getSubjectId();
            }
        }

        return parent::save($options);
    }


    /**
     *  重载newEloquentBuilder方法
     *
     * @desc 查询条件自动加subject条件
     */
    public function newEloquentBuilder($query)
    {
        if (Request::header("mode") == "api") {
            if (Schema::hasColumn($this->getTable(), 'subject_id') && ! Schema::hasColumn($this->getTable(),
                    'top_subject_id')
            ) {
                $subjectId = SubjectUtils::getSubjectId();
                $query->where("subject_id", $subjectId);
            }
        }

        return parent::newEloquentBuilder($query);
    }


    public function getLogoAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        if (starts_with($value, "http")) {
            return $value;
        }

        return config("app.file_url_prefix") . $value;
    }


    public function getImageAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        if (starts_with($value, "http")) {
            return $value;
        }

        return config("app.file_url_prefix") . $value;
    }


    public function setImagesAttribute($values)
    {
        foreach ($values as $key => $value) {
            if (starts_with($value, config("app.file_url_prefix"))) {
                $values[$key] = str_replace(config("app.file_url_prefix"), "", $value);
            }
        }

        $values = json_encode($values);
        $this->attributes['images'] = $values;
    }


    public function getImagesAttribute($value)
    {
        $values = json_decode($value);

        if ($values && count($values) > 0) {
            foreach ($values as $key => $value) {
                if (starts_with($value, "http")) {
                    $values[$key] = $value;
                } else {
                    $values[$key] = config("app.file_url_prefix") . $value;
                }
            }
        } else {
            return [];
        }

        return $values;
    }

}
