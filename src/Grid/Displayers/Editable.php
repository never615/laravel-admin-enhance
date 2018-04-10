<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Displayers;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Displayers\AbstractDisplayer;
class Editable extends \Encore\Admin\Grid\Displayers\Editable
{
    /**
     * Options of editable function.
     *
     * @var array
     */
    protected $options = [
        "emptytext"=>"空"
    ];
}
