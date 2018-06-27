<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Tools;

use Encore\Admin\Grid\Tools\BatchAction;

class CommonBatchAction extends BatchAction
{
    private $url;
    /**
     * @var string
     */
    private $action;

    /**
     * CommonBatchAction constructor.
     *
     * @param        $url
     * @param string $action
     */
    public function __construct($url, $action = "")
    {
        $this->url = $url;
        $this->action = $action;
    }


    public function script()
    {
        $tempUrl = "";
        if (starts_with($this->url, "http")) {
            $tempUrl = $this->url;
        } else {
            $tempUrl = $this->resource."/".$this->url;
        }

        return <<<EOT
$('{$this->getElementClass()}').on('click', function() {

                doAjax("{$tempUrl}", "POST", {
                    _token: LA.token,
                    ids: selectedRows(),
                    action: {$this->action}
                }, function (data) {
                    $.pjax.reload('#pjax-container');
                    layer.msg('操作成功', {icon: 1});
                });
});
EOT;

    }

}
