<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Displayers;

use Encore\Admin\Grid\Displayers\AbstractDisplayer;

class NumberFomart extends AbstractDisplayer
{

    /**
     * @param $value
     *
     * @return string
     */
    public function display($value = 2)
    {
        return number_format($this->value, $value);
    }
}
