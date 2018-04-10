<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data\Traits;




/**
 * Created by PhpStorm.
 * User: never615
 * Date: 08/04/2017
 * Time: 5:20 PM
 */
trait SelectSource{
    public static function selectSourceDate(){
        return static::dynamicData()->pluck("name","id");
    }

}
