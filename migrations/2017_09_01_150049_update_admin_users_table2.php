<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class UpdateAdminUsersTable2 extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("admin_users", function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->json("manager_subject_ids")->nullable()->comment("管理的主体数据范围");
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("admin_users", function ($table) {
            $table->dropColumn('manager_subject_ids');
        });
    }
}
