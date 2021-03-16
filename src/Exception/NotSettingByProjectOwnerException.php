<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Exception;

use Exception;
use Mallto\Tool\Exception\ResourceException;
use Mallto\Tool\Utils\LogUtils;

class NotSettingByProjectOwnerException extends ResourceException
{

    public function __construct(
        $message = null,
        $errors = null,
        Exception $previous = null,
        $headers = [],
        $code = 0
    ) {
        LogUtils::notConfigLogByOwner($message ?: "存在参数未配置");
        parent::__construct($message ?: "存在参数未配置", $errors, $previous, $headers, $code);
    }
}
