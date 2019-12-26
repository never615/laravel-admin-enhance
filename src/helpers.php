<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

use Illuminate\Support\Facades\Lang;

if ( ! function_exists('array_dot2')) {

    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param        $array
     * @param array  $ignores ,忽略转成数据的字段,把忽略的数组转换成json字符串输出
     * @param string $prepend
     *
     * @return array
     */
    function array_dot2($array, $ignores = [], $prepend = '')
    {
        $results = [];

        foreach ($array as $key => $value) {
            if ( ! empty($prepend)) {
                //处理ignore设置为xxx.yyy的情况
                $ignores = array_map(function ($ignore) use ($prepend) {
                    if (starts_with($ignore, $prepend)) {
                        return str_replace($prepend, "", $ignore);
                    } else {
                        return $ignore;
                    }
                }, $ignores);
            }

            if (in_array($key, $ignores) && is_array($value)) {
                $value = json_encode($value);
            }

            if (is_array($value) && ! empty($value)) {
                $results = array_merge($results, array_dot2($value, $ignores, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }
}

if ( ! function_exists('admin_e_url')) {
    /**
     * Get admin url.
     *
     * @param string $path
     *
     * @param bool   $security
     *
     * @return string
     */
    function admin_e_url($path = '', $security = true)
    {
        $prefix = trim(config('admin.route.prefix'), '/');

        return url($prefix ? "/$prefix" : '', [], $security) . '/' . trim($path, '/');
    }
}

if ( ! function_exists('arr_is_unique')) {
    function arr_is_unique($arr, $key)
    {
        $tmp_arr = [];
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

if ( ! function_exists('data_source_url')) {
    /**
     * 获取请求数据源的链接
     *
     * @param string $key 请求的关键字
     *
     * @return string
     */
    function data_source_url($key = '')
    {
        return "/admin/select_data/" . $key;
    }
}

if ( ! function_exists('mt_trans')) {
    /**
     * @param $key
     *
     * @return array|\Illuminate\Contracts\Translation\Translator|null|string
     * @deprecated
     */
    function mt_trans($key)
    {
        $temp = "validation.attributes." . $key;
        if (Lang::has($temp)) {
            return trans("validation.attributes." . $key);
        } else {
            return $key;
        }
    }
}


