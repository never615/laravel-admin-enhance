<?php

namespace Mallto\Admin\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class AdminE.
 *
 * @method static array getSubjectConfigClass()
 * @method static void extendSubjectConfigClass($class)
 * @method static array getSubjectSettingClass()
 * @method static void extendSubjectSettingClass($class)
 * @method static array getSelectSourceClass()
 * @method static void  extendSelectSourceClass($class)
 * @method static void quickAccess()
 * @method static void adminBootstrap()
 * @method static void extend($name, $class)
 * @method static void menu()
 * @method static void registerAuthRoutes(\Closure $builder)
 *
 * @see \Mallto\Admin\AdminE
 */
class AdminE extends Facade
{

    protected static function getFacadeAccessor()
    {
        return \Mallto\Admin\AdminE::class;
    }
}
