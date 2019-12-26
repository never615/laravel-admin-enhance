<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPath extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->string("path")->nullable();
        });

        Schema::table('admin_menu', function (Blueprint $table) {
            $table->string("path")->nullable();
        });

        Schema::table('admin_permissions', function (Blueprint $table) {
            $table->string("path")->nullable();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn("path");
        });
        Schema::table('admin_menu', function (Blueprint $table) {
            $table->dropColumn("path");
        });
        Schema::table('admin_permissions', function (Blueprint $table) {
            $table->dropColumn("path");
        });
    }
}
