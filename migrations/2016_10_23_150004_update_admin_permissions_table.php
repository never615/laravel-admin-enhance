<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * 升级权限表支持树结构
 * Class UpdateAdminPermissionsTable
 */
class UpdateAdminPermissionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(config('admin.database.permissions_table'), function ($table) {
            $table->unsignedInteger('parent_id')->default(0);
            $table->integer("order")->default(0);
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(config('admin.database.permissions_table'), function ($table) {
            $table->dropColumn('parent_id');
            $table->dropColumn('order');
        });
    }
}
