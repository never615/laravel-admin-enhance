<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Exception;

use Exception;
use Mallto\Tool\Utils\LogUtils;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * 项目拥有者必须要配置的没配置的,调用该异常,会有短信报警
 *
 * Class PermissionDeniedException
 *
 * @package App\Exceptions
 */
class SubjectConfigException extends HttpException
{

    public function __construct($message = null, Exception $previous = null, $headers = [], $code = 0)
    {
        LogUtils::notConfigLogByOwner($message ?: "有参数未配置");

        parent::__construct(422, $message ?: "有参数未配置", $previous, $headers, $code);
    }
}
