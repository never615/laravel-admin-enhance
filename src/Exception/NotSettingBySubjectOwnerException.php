<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Exception;


use Exception;
use Mallto\Tool\Exception\ResourceException;
use Mallto\Tool\Utils\LogUtils;

class NotSettingBySubjectOwnerException extends ResourceException
{

    public function __construct(
        $message = null,
        $code = 0,
        $errors = null,
        Exception $previous = null,
        $headers = []
    ) {
        LogUtils::notConfigLogBySubjecter($message ?: "存在参数未配置");
        parent::__construct($message ?: "存在参数未配置", $errors, $previous, $headers, $code);
    }
}
