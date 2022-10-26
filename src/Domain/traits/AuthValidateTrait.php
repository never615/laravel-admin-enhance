<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: never615 <never615.com>
 * Date: 02/11/2017
 * Time: 2:56 PM
 */

namespace Mallto\Admin\Domain\Traits;

use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

trait AuthValidateTrait
{

    /**
     * 检查是不是微信请求
     *
     * @param $request
     */
    protected function isWechatRequest($request)
    {
        if ($request->header("REQUEST-TYPE") != "WECHAT") {
            throw new PreconditionFailedHttpException(trans("errors.precondition_failed"));
        }
    }


    /**
     * 检查是不是支付宝请求
     *
     * @param $request
     */
    protected function isAliRequest($request)
    {
        if ($request->header("REQUEST-TYPE") != "ALI") {
            throw new PreconditionFailedHttpException(trans("errors.precondition_failed"));
        }
    }
}
