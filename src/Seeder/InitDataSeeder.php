<?php

namespace Mallto\Admin\Seeder;

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Auth\Database\Role;
use Illuminate\Database\Seeder;
use Mallto\Admin\Data\Subject;

class InitDataSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        if (Subject::count() > 0) {
            return;
        }

        /**
         * --------------------   Subject create  -------------------------
         */
        $系统维护Subject = Subject::create([
            'name' => "系统维护",
            'uuid' => 999,
        ]);

        /**
         * -----------------------  Role create  --------------------------------
         */
        $ownerRole = Role::create([
            "name"       => "维护角色",
            "slug"       => "owner",
            "subject_id" => $系统维护Subject->id,
        ]);

        /**
         * --------------------------------  Admin_user create   ------------------------------
         */
        $mallto = Administrator::create([
            'username'       => 'system',
            'password'       => bcrypt('system'),
            'name'           => '维护人员',
            "subject_id"     => $系统维护Subject->id,
            "adminable_id"   => $系统维护Subject->id,
            "adminable_type" => "subject",
        ]);
        // add role to user.
        $mallto->roles()->save($ownerRole);

    }
}
