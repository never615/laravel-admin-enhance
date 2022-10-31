<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Request;

class WelcomeController extends Controller
{

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index(Request $request)
    {
        return view('adminE::welcome');
    }

}
