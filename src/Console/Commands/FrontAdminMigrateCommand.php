<?php
/*
 * Copyright (c) 2025. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Console\Commands;

use Illuminate\Console\Command;
use Mallto\Admin\Data\Administrator;
use Mallto\Admin\Data\Role;

class FrontAdminMigrateCommand extends Command
{
    protected $signature = 'front-admin:migrate';

    protected $description = 'Migrate map related admin users and roles to front tables';

    public function handle(): void
    {
        $roles = Role::query()
            ->where('name', 'like', '%地图%')
            ->with('apiPermissions', 'administrators')
            ->get();

        foreach ($roles as $role) {
            $role->pure_front = true;
            $role->save();

            foreach ($role->administrators as $adminUser) {
                $adminUser->pure_front = true;
                $adminUser->save();
            }
        }

        $mapUsers = Administrator::query()
            ->where('username', 'like', '%map%')
            ->get();

        foreach ($mapUsers as $adminUser) {

            $adminUser->pure_front = true;
            $adminUser->save();

            foreach ($adminUser->roles as $role) {
                $role->pure_front = true;
                $role->save();
            }
        }

        $this->info('Front admin migration completed.');
    }


}
