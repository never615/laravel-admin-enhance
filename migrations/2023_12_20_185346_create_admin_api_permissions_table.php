<?php
/**
 * Copyright (c) 2023. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminApiPermissionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_api_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50)->unique();
            $table->string('slug', 50)->unique();
            $table->string('http_method')->nullable();
            $table->text('http_path')->nullable();

            $table->text("describe")->nullable();
            $table->boolean("common")->default(false)->comment("是否是所有主体都拥有的权限,必须设置到权限组上");
            $table->unsignedInteger('parent_id')->default(0);
            $table->string("path")->nullable();

            $table->integer("order")->default(0);

            $table->unsignedInteger("admin_user_id")->nullable();
            $table->foreign('admin_user_id')->references('id')->on('admin_users');

            $table->timestamps();
        });

        Schema::create('admin_role_api_permissions', function (Blueprint $table) {
            $table->integer('role_id');
            $table->integer('permission_id');
            $table->index(['role_id', 'permission_id']);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('admin_api_permissions');
        Schema::drop('admin_role_api_permissions');
    }
}
