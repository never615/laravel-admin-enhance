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
        $项目管理Subject = Subject::create([
            'name' => "项目管理方",
            'uuid' => 999,
        ]);

        $招商Subject = Subject::create([
            'name'      => "招商集团",
            "parent_id" => $项目管理Subject->id,
        ]);

        $蛇口Subject = Subject::create([
            'name'      => "蛇口花园城",
            "parent_id" => $招商Subject->id,
        ]);

        /**
         * -----------------------  Role create  --------------------------------
         */
        $ownerRole = Role::create([
            "name"       => "项目拥有者",
            "slug"       => "owner",
            "subject_id" => $项目管理Subject->id,
        ]);

        $bigAdminRole = Role::create([
            "name"       => "招商管理员",
            "slug"       => "admin",
            "subject_id" => $招商Subject->id,
        ]);

        $commonAdminRole = Role::create([
            "name"       => "蛇口花园城管理员",
            "slug"       => "admin",
            "subject_id" => $蛇口Subject->id,
        ]);

        /**
         * --------------------------------  Admin_user create   ------------------------------
         */
        $mallto = Administrator::create([
            'username'       => 'mallto',
            'password'       => bcrypt('mallto'),
            'name'           => '系统管理',
            "subject_id"     => $项目管理Subject->id,
            "adminable_id"   => $项目管理Subject->id,
            "adminable_type" => "subject",
        ]);

        $招商 = Administrator::create([
            'username'       => 'zhaoshang',
            'password'       => bcrypt('zhaoshang'),
            'name'           => '招商地产管理',
            "subject_id"     => $招商Subject->id,
            "adminable_id"   => $招商Subject->id,
            "adminable_type" => "subject",
        ]);

        $gardencity = Administrator::create([
            'username'       => 'gardencity',
            'password'       => bcrypt('gardencity'),
            'name'           => '花园城管理',
            "subject_id"     => $蛇口Subject->id,
            "adminable_id"   => $蛇口Subject->id,
            "adminable_type" => "subject",
        ]);

        // add role to user.
        $mallto->roles()->save($ownerRole);
        $招商->roles()->save($bigAdminRole);
        $gardencity->roles()->save($commonAdminRole);


    }
}
