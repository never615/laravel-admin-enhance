<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Tools;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 *
 *
 *
 * Created by PhpStorm.
 * User: never615
 * Date: 31/05/2017
 * Time: 2:36 PM
 */
class BatchStatus extends \Encore\Admin\Grid\Tools\BatchAction
{

    protected $status;

    /**
     * @var null
     */
    private $url;


    /**
     * BatchPass constructor.
     *
     * @param string $status
     * @param null   $url 默认的请求地址为当前资源的url后拼接/status,如:https://integration-easy.mall-to.com/admin/subjects/status
     */
    public function __construct($status, $url = null)
    {
        if (empty($status)) {
            throw new HttpException('422', "未设置状态");
        }
        $this->status = $status;
        $this->url = $url;
    }


    public function script()
    {
        if (is_null($this->url)) {
            $this->url = $this->resource . '/status';
        }

        return <<<EOT
$('{$this->getElementClass()}').on('click', function() {

    doAjax('{$this->url}',"POST",{
        _token:LA.token,
        ids: selectedRows().join(),
        action: '{$this->status}'
    },function(data){
        $.pjax.reload('#pjax-container');
        toastr.success('操作成功');
        //layer.msg('操作成功', {icon: 1});
    });
});

EOT;

    }
}
