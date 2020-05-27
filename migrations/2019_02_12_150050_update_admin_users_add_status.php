<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * 管理端账号添加状态
 * Class UpdateAdminUserAddStatus
 */
class UpdateAdminUsersAddStatus extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("admin_users", function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->string("status")->default("normal");
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("admin_users", function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn("status");
        });
    }
}
