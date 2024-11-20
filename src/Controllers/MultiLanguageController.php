<?php
/*
 * Copyright (c) 2023. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

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
            return response('ok')->cookie($cookie_name, $locale);
        }
    }
}