<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class UpdateSubjects6Table extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("subjects", function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->boolean("base")->default(false)->comment("是否是总部");
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("subjects", function ($table) {
            $table->dropColumn('base');
        });
    }
}
