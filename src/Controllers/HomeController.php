<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Routing\Controller;

class HomeController extends Controller
{

    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('数据看板');

            $user = Admin::user();
            if ( ! $user->can("dashboard")) {
                $content->description("没有权限查看数据看板");

                return;
            } else {
                $content->description('敬请期待');
            }

        });
    }
}
