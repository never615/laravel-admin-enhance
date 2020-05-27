<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Tools;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;

class BackButton extends AbstractTool
{

    /**
     * Render CreateButton.
     *
     * @return string
     */
    public function render()
    {
        $new = trans('admin.back');

        $script = <<<'EOT'
$('.table-history-back').on('click', function (event) {
    event.preventDefault();
    history.back(1);
});
EOT;

        Admin::script($script);

        return <<<EOT

<div class="btn-group pull-right" style="margin-right: 10px">
    <a class="btn btn-sm btn-default table-history-back">
        <i class="fa fa-arrow-left"></i>&nbsp;&nbsp;{$new}
    </a>
</div>

EOT;
    }
}
