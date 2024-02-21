<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: never615
 * Date: 10/03/2017
 * Time: 8:36 PM
 *
 * You need set permission's slug by outeName or url( auth/roles of https://xxx.com/admin/auth/roles )
 */

namespace Mallto\Admin\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;


class MultiLanguageMiddleware
{

    public function handle($request, Closure $next)
    {
        $languages = config("admin.multi-language.languages");
        $cookie_name = config('admin.multi-language.cookie-name', 'locale');

        if (Cookie::has($cookie_name) && array_key_exists(Cookie::get($cookie_name), $languages)) {
            App::setLocale(Cookie::get($cookie_name));
        } else {
            $default = config('admin.multi-language.default');
            App::setLocale($default);
        }
        return $next($request);
    }

}
