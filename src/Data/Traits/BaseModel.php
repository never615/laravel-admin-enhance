<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data\Traits;

use Illuminate\Database\Eloquent\Model;
use Mallto\Admin\AdminUtils;
use Mallto\Admin\Data\Administrator;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\Data\SubjectSetting;

/**
 * Created by PhpStorm.
 * User: never615
 * Date: 21/04/2017
 * Time: 5:13 PM
 */
abstract class BaseModel extends Model
{

    use DynamicData, SelectSource;

    protected $hidden = [ 'deleted_at' ];

    protected $guarded = [];

    protected $casts = [
        'images' => 'array',
    ];


    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }


    public function subjectSetting()
    {
        return $this->belongsTo(SubjectSetting::class, 'subject_id', 'subject_id');
    }


    public function getIconAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        if (starts_with($value, "http")) {
            return $value;
        }

        $url = config("app.file_url_prefix") . $value;
        if (AdminUtils::isAdminRequest() || str_contains($url, "?")) {
            return $url;
        } else {
            return $url . '?imageView2/0/interlace/1/q/75|imageslim';
        }
    }


    public function getLogoAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        if (starts_with($value, "http")) {
            return $value;
        }

        $url = config("app.file_url_prefix") . $value;
        if (AdminUtils::isAdminRequest() || str_contains($url, "?")) {
            return $url;
        } else {
            return $url . '?imageView2/0/interlace/1/q/75|imageslim';
        }
    }


    public function getImageAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        if (starts_with($value, "http")) {
            return $value;
        }

        $url = config("app.file_url_prefix") . $value;
        if (AdminUtils::isAdminRequest() || str_contains($url, "?")) {
            return $url;
        } else {
            return $url . '?imageView2/0/interlace/1/q/75|imageslim';
        }
    }


    public function setImagesAttribute($values)
    {
        foreach ($values as &$value) {
            if (starts_with($value, config("app.file_url_prefix"))) {
                $url = str_replace(config("app.file_url_prefix"), "", $value);
                if (str_contains($url, "?")) {
                    $tmpUrls = explode("?", $url);
                    $url = $tmpUrls[0];
                }
                $value = $url;
            }
        }

        $values = array_values($values);

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

                    $url = config("app.file_url_prefix") . $value;
                    if (AdminUtils::isAdminRequest() || str_contains($url, "?")) {
                        $values[$key] = $url;
                    } else {
                        $values[$key] = $url . '?imageView2/0/interlace/1/q/75|imageslim';
                    }
                }
            }
        } else {
            return [];
        }

        return $values;
    }


    public function admin()
    {
        return $this->belongsTo(Administrator::class, "admin_user_id");
    }

}
