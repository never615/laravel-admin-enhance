<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * 导入记录
 * Class CreateImportRecordsTable
 */
class UpdateImportRecordsAddExtra extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("import_records", function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->json("extra")->nullable();
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
        Schema::table("import_records", function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn("extra");
            $table->dropColumn("remark");
        });
    }
}
