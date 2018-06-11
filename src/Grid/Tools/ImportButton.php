<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Tools;


use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;

class ImportButton extends AbstractTool
{

    /**
     * Set up script for export button.
     */
    protected function setUpScripts()
    {

        $script = <<<'SCRIPT'

$('.table-import').click(function (e) {
    e.preventDefault();
    
    var path = window.location.pathname;
    var paths=path.split("/");
    var lastPath=paths.pop();
    window.location.href="/admin/import_records/create?module_slug="+lastPath;
});

SCRIPT;

        Admin::script($script);
    }

    /**
     * Render Export button.
     *
     * @return string
     */
    public function render()
    {

        $this->setUpScripts();

        $import = "导入";

        return <<<EOT
<div class="btn-group pull-right" style="margin-right: 10px">
    <button class="btn btn-sm btn-twitter table-import">
        <i class="fa fa-upload"></i>&nbsp;&nbsp;{$import}
    </button>
</div>
&nbsp;&nbsp;

EOT;
    }
}
