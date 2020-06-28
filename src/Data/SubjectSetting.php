<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;

use Mallto\Admin\Data\Traits\BaseModel;

class SubjectSetting extends BaseModel
{

    /**
     * 银闪付网关
     */
    const UNION_PAY_DRIVER = [
        'UnionPay_Express' => '银联全产品网关(PC，APP，WAP支付)',
    ];

    /**
     * 证书版本
     */
    const UNION_PAY_CERT_VERSION = [
        '5.0.0' => '5.0.0版本',
        '5.1.0' => '5.1.0版本',
    ];

    protected $guarded = [];

}
