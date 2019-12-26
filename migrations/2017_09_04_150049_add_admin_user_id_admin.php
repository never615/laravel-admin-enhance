<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddAdminUserIdAdmin extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("admin_users", function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->unsignedInteger("admin_user_id")->nullable();
//            $table->foreign('admin_user_id')->references('id')->on('admin_users')->onDelete('SET NULL');
        });

        Schema::table("admin_menu", function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->unsignedInteger("admin_user_id")->nullable();
//            $table->foreign('admin_user_id')->references('id')->on('admin_users')->onDelete('SET NULL');
        });

        Schema::table("admin_permissions", function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->unsignedInteger("admin_user_id")->nullable();
//            $table->foreign('admin_user_id')->references('id')->on('admin_users')->onDelete('SET NULL');
        });

        Schema::table("admin_roles", function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->unsignedInteger("admin_user_id")->nullable();
//            $table->foreign('admin_user_id')->references('id')->on('admin_users')->onDelete('SET NULL');
        });

        Schema::table("subjects", function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->unsignedInteger("admin_user_id")->nullable();
//            $table->foreign('admin_user_id')->references('id')->on('admin_users')->onDelete('SET NULL');
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
            $table->dropColumn("admin_user_id");
        });

        Schema::table("admin_menu", function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn("admin_user_id");
        });

        Schema::table("admin_permissions", function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn("admin_user_id");
        });

        Schema::table("admin_roles", function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn("admin_user_id");
        });

        Schema::table("subjects", function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn("admin_user_id");
        });
    }
}
