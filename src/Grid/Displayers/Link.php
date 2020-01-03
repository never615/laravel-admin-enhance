<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Displayers;

use Encore\Admin\Grid\Displayers\AbstractDisplayer;

class Link extends AbstractDisplayer
{

    public function display($href = '', $target = '_blank')
    {

        if (is_callable($href)) {
            $href = $href->bindTo($this);
            $href = $href();
        } else {
            $href = $href ?: $this->value;
        }
        if ($href) {
            return "<a href='$href' target='$target'>{$this->value}</a>";
        } else {
            return $this->value;
        }

    }
}
