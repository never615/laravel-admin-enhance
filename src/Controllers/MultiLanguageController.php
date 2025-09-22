<?php
/*
 * Copyright (c) 2023. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Mallto\Admin\CacheUtils;

/**
 * User: never615 <never615.com>
 * Date: 2023/10/9
 * Time: 18:46
 */
class MultiLanguageController extends Controller
{
    public function locale(Request $request)
    {
        $locale = $request->get('locale');
        $languages = config("admin.multi-language.languages");
        $cookie_name = config('admin.multi-language.cookie-name', 'locale');

        if (array_key_exists($locale, $languages)) {
            // 添加过期时间，30天
            Config::set('app.locale', $locale);

            // 清除菜单缓存，确保所有用户都能看到更新后的语言菜单
            CacheUtils::clearMenuCache();

            return response('ok')
                ->cookie($cookie_name, $locale, 60 * 24 * 30, '/', null, false, false);
        }
        // 对于不支持的语言，返回错误响应
        return response('Invalid locale', 400);
    }
}