<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Exception;

use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 *
 * Class ThirdPartException
 *
 * @package App\Exceptions
 */
class SubjectNotFoundException extends HttpException
{

    public function __construct($message = null, Exception $previous = null, $headers = [], $code = 0)
    {
        parent::__construct(428, $message ?: "不支持该公众号访问,未找到相应主体", $previous, $headers, $code);
    }
}
