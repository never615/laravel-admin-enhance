<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data\Traits;

/**
 * Created by PhpStorm.
 * User: never615
 * Date: 21/04/2017
 * Time: 5:21 PM
 */
trait ImagePrefix
{

    public function getLogoAttribute($value)
    {
        return config("app.file_url_prefix") . $value;
    }


    public function getImageAttribute($value)
    {
        return config("app.file_url_prefix") . $value;
    }

//    public function getImagesAttribute($values)
//    {
//
//        foreach ($values as $key => $value) {
//            $values[$key] = config("app.file_url_prefix").$value;
//
//        }
//
//        return $values;
//    }
}
