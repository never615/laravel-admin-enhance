<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateAdminMenuSubjectsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("admin_menu_subjects", function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->unsignedInteger("admin_menu_id");
            $table->unsignedInteger("subject_id");
        });

        Schema::table("admin_menu", function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->string("sub_title")->nullable();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("admin_menu_subjects");

        Schema::table("admin_menu", function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn("sub_title");
        });
    }
}
