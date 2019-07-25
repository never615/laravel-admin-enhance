<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Form\Field;


use Encore\Admin\Form\Field;

class Display extends Field
{

    /**
     * If this field should render.
     *
     * @return bool
     */
    protected function shouldRender()
    {
        if (!$this->display) {
            return false;
        }

        if ($this->form && $this->form->model()->getKey()) {
            return true;
        } else {
            return false;
        }
    }
}
