<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Tools;

use Encore\Admin\Grid\Tools\AbstractTool;

class ListButton extends AbstractTool
{

    /**
     * @var
     */
    private $name;

    /**
     * @var
     */
    private $link;


    /**
     * ListButton constructor.
     *
     * @param $name
     * @param $link
     */
    public function __construct($name, $link)
    {
        $this->name = $name;
        $this->link = $link;
    }


    /**
     * Render CreateButton.
     *
     * @return string
     */
    public function render()
    {
        $new = $this->name ?: trans("lang.list");

        return <<<EOT

<div class="btn-group pull-right" style="margin-right: 10px">
    <a href="$this->link" class="btn btn-sm btn-default">
        <i class="fa fa-list"></i>&nbsp;&nbsp;{$new}
    </a>
</div>

EOT;
    }
}
