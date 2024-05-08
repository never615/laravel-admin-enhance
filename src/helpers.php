<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

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
     * Now you can add your own translate files for your project.
     * The "laravel-admin" will search for the translations in these sequence:
     * A.) admin.modelName.columnName
     * B.) admin.columnName
     * C.) Column name with spaces (dots and underscore replaced with spaces)
     * D.) Fallback
     * If you have translation A, that will be used, if not then B.
     * If there is no translation at all:
     * if exists the fallback D else the C will be the output.
     *
     * @param      $modelPath
     * @param      $column
     * @param null $fallback
     *
     * @return string
     */
    function mt_trans($column, $modelPath = "", $labelFormat = false, $fallback = null)
    {
        $modelName = "";
        if ($modelPath) {

            $nameList = explode('\\', $modelPath);
            /*
             * CamelCase model name converted to underscore name version.
             * ExampleString => example_strinig
             */
            $modelName = ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', end($nameList))),
                '_');
        }

        if (str_contains($column, ".")) {
            try {
                $tempKeys = explode(".", $column);

                return admin_translate($tempKeys[1], $tempKeys[0]);
            } catch (\Exception $exception) {
                Log::warning("admin_translate");
                Log::warning($exception);
            }
        }

        /*
         * ExampleString with banana => example_string_with_banana
         */
        $columnLower = ltrim(strtolower(preg_replace('/[A-Z ]([A-Z](?![a-z]))*/', '_$0', $column)), '_');
        $columnLower = str_replace(' ', '', $columnLower);

        /*
         * The possible translate keys in priority order.
         */
        $transLateKeys = [
            'admin2.' . $modelName . '.' . $columnLower,
            'admin2.' . str_plural($modelName) . '.' . $columnLower,
            'admin2.' . $columnLower,
            'admin2.table.' . $columnLower,
            'validation.attributes.' . $columnLower,
            'admin.' . $modelName . '.' . $columnLower,
//            'admin.'.str_plural($modelName).'.'.$columnLower,
            'admin.' . $columnLower,
        ];

        $label = null;
        foreach ($transLateKeys as $key) {
            if (Lang::has($key, config('other.diy_locale')) && is_string(trans($key, [],
                    config('other.diy_locale')))) {
                //if (is_string(trans($key, [], config('other.diy_locale')))) {
                $label = trans($key, [], config('other.diy_locale'));
                break;
            }
        }
        if ( ! $label) {
            if ($labelFormat) {
                //$label = str_replace(['.', '_'], ' ', $fallback ? $fallback : ucfirst($column));
                $label = str_replace([ '.', '_' ], ' ', $fallback ? $fallback : $column);
            } else {
                $label = $column;
            }
        }

        return (string) $label;
    }
}


