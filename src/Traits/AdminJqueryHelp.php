<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Traits;

use Encore\Admin\Facades\Admin;

/**
 * Trait AdminFilterHelp
 *
 * @package Mallto\Admin\Traits
 */
trait  AdminJqueryHelp
{

    /**
     * $filter根据$column查找元素并添加tips
     *
     * @param $column
     * @param $message
     */
    protected function filterShowTips($column, $message)
    {
        $script = <<<SCRIPT
$(document).ready(function () {
        $(".control-label:contains({$column})").attr("data-toggle", "tooltip");
        $(".control-label:contains({$column})").attr("data-placemen", "right");
        $(".control-label:contains({$column})").attr("data-html", "true");
        $(".control-label:contains({$column})").attr("data-title", "{$message}");
});
SCRIPT;

        Admin::script($script);
    }
}
