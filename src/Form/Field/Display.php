<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Form\Field;

use Encore\Admin\Form\Field;

/**
 * 创建是不会显示
 * Class Display
 *
 * @package Mallto\Admin\Form\Field
 */
class Display extends Field
{

    /**
     * @var bool
     */
    protected $display = false;


    /**
     * If this field should render.
     *
     * @return bool
     */
    protected function shouldRender(): bool
    {
        if ($this->display) {
            return true;
        }

        if ($this->form && $this->form->model()->getKey()) {
            return true;
        } else {
            return false;
        }
    }
}
