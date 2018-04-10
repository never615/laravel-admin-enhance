<?php
namespace Mallto\Admin\Facades;
use Illuminate\Support\Facades\Facade;

class AdminE extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mallto\Admin\AdminE::class;
    }
}
