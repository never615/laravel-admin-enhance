<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class UpdateSubjectConfigsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("subject_configs", function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->text("remark")->nullable();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("subject_configs", function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn("remark");
        });
    }
}
