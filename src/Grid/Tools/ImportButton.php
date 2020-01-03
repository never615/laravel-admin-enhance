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
    private $moduleSlug;

    /**
     * @var null
     */
    private $url;


    /**
     * ImportButton constructor.
     *
     * @param string $moduleSlug 导入任务处理者标识,默认使用引入按钮的的页面的url最后一段
     *                           如 `http://xxxx.com/admin/member_cards`中的member_cards
     * @param null   $url        点击导入按钮跳转到的页面
     */
    public function __construct($moduleSlug = null, $url = "/admin/import_records/create")
    {
        $this->moduleSlug = $moduleSlug;
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
    var moduleSlug="$this->moduleSlug";
    var path = window.location.pathname;
    var paths=path.split("/");
    var lastPath=paths.pop();
    window.open("$this->url?module_slug="+(moduleSlug?moduleSlug:lastPath));
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
