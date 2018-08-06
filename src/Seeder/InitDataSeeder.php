<?php

namespace Mallto\Admin\Seeder;


use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Auth\Database\Role;
use Mallto\Admin\Data\Subject;
use Illuminate\Database\Seeder;

class InitDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $subject = Subject::whereIn("name", ["项目管理", "墨兔科技", "深圳墨兔"])
            ->first();
        if ($subject) {
            return;
        }

        /**
         * --------------------   Subject create  -------------------------
         */
        Subject::create([
            'name' => "项目管理",
        ]);

        Subject::create([
            'name'      => "招商集团",
            "parent_id" => 1,
        ]);

        Subject::create([
            'name'      => "蛇口花园城",
            "parent_id" => 2,
        ]);

        /**
         * -----------------------  Role create  --------------------------------
         */
        $ownerRole = Role::create([
            "name"       => "项目拥有者",
            "slug"       => "owner",
            "subject_id" => 1,
        ]);

        $bigAdminRole = Role::create([
            "name"       => "招商管理员",
            "slug"       => "admin",
            "subject_id" => 2,
        ]);

        $commonAdminRole = Role::create([
            "name"       => "蛇口花园城管理员",
            "slug"       => "admin",
            "subject_id" => 3,
        ]);

        /**
         * --------------------------------  Admin_user create   ------------------------------
         */
        $mallto = Administrator::create([
            'username'       => 'mallto',
            'password'       => bcrypt('mallto'),
            'name'           => '深圳墨兔管理',
            "subject_id"     => 1,
            "adminable_id"   => 1,
            "adminable_type" => "subject",
        ]);

        $招商 = Administrator::create([
            'username'       => 'zhaoshang',
            'password'       => bcrypt('zhaoshang'),
            'name'           => '招商地产管理',
            "subject_id"     => 2,
            "adminable_id"   => 2,
            "adminable_type" => "subject",
        ]);

        $seaworld = Administrator::create([
            'username'       => 'gardencity',
            'password'       => bcrypt('gardencity'),
            'name'           => '花园城管理',
            "subject_id"     => 3,
            "adminable_id"   => 3,
            "adminable_type" => "subject",
        ]);


        // add role to user.
        $mallto->roles()->save($ownerRole);
        $招商->roles()->save($bigAdminRole);
        $seaworld->roles()->save($commonAdminRole);


    }
}
