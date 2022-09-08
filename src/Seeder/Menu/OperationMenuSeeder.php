<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Seeder\Menu;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Route;
use Mallto\Admin\Data\Menu;
use Mallto\Admin\Data\OperationLogDictionary;
use Mallto\Admin\Seeder\MenuSeederMaker;

class OperationMenuSeeder extends Seeder
{

    use MenuSeederMaker;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menus = Menu::query()->get();
        foreach ($menus as $item => $branch) {
            if ($branch['uri']) {
                if (Route::has($branch['uri'])) {
                    $uri = route($branch['uri'], [], false);
                    if ($uri) {
                        OperationLogDictionary::query()->updateOrCreate([
                            'path' => ltrim($uri, '/'),
                            'name' => $branch['title'],
                        ]);
                    }
                }
            }
        }
    }

}
