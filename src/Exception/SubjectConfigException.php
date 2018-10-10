<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Exception;


use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * 第三方服务异常
 * Class PermissionDeniedException
 *
 * @package App\Exceptions
 */
class SubjectConfigException extends HttpException
{

    public function __construct($message = null, Exception $previous = null, $headers = [], $code = 0)
    {
        parent::__construct(403, $message?:"有参数未配置", $previous, $headers, $code);
    }
}
