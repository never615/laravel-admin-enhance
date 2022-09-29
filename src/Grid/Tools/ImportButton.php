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
     * @var null|string
     */
    private $importHandler;

    /**
     * @var null
     */
    private $url;


    /**
     * ImportButton constructor.
     *
     * @param string $importHandler 导入任务处理类
     * @param string $url           点击导入按钮跳转到的页面
     */
    public function __construct($importHandler, $url = "/admin/import_records/create")
    {
        $this->importHandler = urlencode($importHandler);
        $this->url = $url;
    }


    /**
     * Set up script for export button.
     */
    protected function setUpScripts()
    {
        $script = <<<EOF
$('.table-import').click(function (e) {
    e.preventDefault();
    var importHandler="$this->importHandler";
    window.open("$this->url?import_handler="+importHandler);
});

EOF;

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
