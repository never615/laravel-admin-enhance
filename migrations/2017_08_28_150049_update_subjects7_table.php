<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class UpdateSubjects7Table extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("subjects", function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->json("extra_config")->nullable()->comment("额外的配置");
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
            $table->dropColumn('extra_config');
        });
    }
}
