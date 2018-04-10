<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */


if (!function_exists('admin_url')) {
    /**
     * Get admin url.
     *
     * @param string $path
     *
     * @param bool   $security
     * @return string
     */
    function admin_e_url($path = '', $security = true)
    {
        if (config("app.http_protocol") == "https") {
            $security = true;
        } else {
            $security = false;
        }
        $prefix = trim(config('admin.route.prefix'), '/');

        return url($prefix ? "/$prefix" : '', [], $security).'/'.trim($path, '/');
    }
}





if (!function_exists('arr_is_unique')) {
    function arr_is_unique($arr, $key)
    {
        $tmp_arr = array ();
        foreach ($arr as $k => $v) {
            if (in_array($v[$key], $tmp_arr))//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
            {
                return false;
            } else {
                $tmp_arr[] = $v[$key];
            }
        }

        return true;
    }
}


if (!function_exists('data_source_url')) {
    /**
     * 获取请求数据源的链接
     *
     * @param string $key 请求的关键字
     *
     * @return string
     */
    function data_source_url($key = '')
    {
        return "/admin/select_data/".$key;
    }
}


if (!function_exists('mt_trans')) {
    /**
     * @deprecated
     * @param $key
     * @return array|\Illuminate\Contracts\Translation\Translator|null|string
     */
    function mt_trans($key)
    {
        $temp = "validation.attributes.".$key;
        if (Lang::has($temp)) {
            return trans("validation.attributes.".$key);
        } else {
            return $key;
        }
    }
}

