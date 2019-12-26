<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

/**
 * select,作为source源.
 * 数组中的key是value;数组中的value是text.
 *
 * Created by PhpStorm.
 * User: never615
 * Date: 17/11/2016
 * Time: 7:32 PM
 */

namespace Mallto\Admin;

class SelectConstants
{

    //开关的数据源
    const SWITCH_STATES = [
        'on'  => [ 'value' => 1, 'text' => '打开', 'color' => 'primary' ],
        'off' => [ 'value' => 2, 'text' => '关闭', 'color' => 'default' ],
    ];

    //是否
    const YES_OR_NO = [
        1 => "是",
        0 => "否",
    ];

    //管理端用来选择账号类型的select
    const ADMINABLE_TYPE = [
        'subject' => '主体',
    ];

    const GENGDER = [
        '1' => "男",
        '2' => "女",
        "0" => "未知",
    ];

}
