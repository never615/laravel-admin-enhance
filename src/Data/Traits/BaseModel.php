<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data\Traits;



use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: never615
 * Date: 21/04/2017
 * Time: 5:13 PM
 */
abstract class BaseModel extends Model
{
    use DynamicData, SelectSource;


    protected $hidden = ['deleted_at'];

    protected $guarded = [];


    public function getLogoAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        if (starts_with($value, "http")) {
            return $value;
        }

        return config("app.file_url_prefix").$value;
    }

    public function getImageAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        if (starts_with($value, "http")) {
            return $value;
        }

        return config("app.file_url_prefix").$value;
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

        if ($values&&count($values) > 0) {
            foreach ($values as $key => $value) {
                if (starts_with($value, "http")) {
                    $values[$key] = $value;
                } else {
                    $values[$key] = config("app.file_url_prefix").$value;
                }
            }
        } else {
            return [];
        }

        return $values;
    }

}
