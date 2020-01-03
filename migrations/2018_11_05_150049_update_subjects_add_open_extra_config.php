<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class UpdateSubjectsAddOpenExtraConfig extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("subjects", function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->json("open_extra_config")
                ->nullable()
                ->comment("非项目拥有者可以编辑的额外的配置,还有extra_config一半用做项目拥有者才能编辑的配置");
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
            $table->dropColumn('open_extra_config');
        });
    }
}
