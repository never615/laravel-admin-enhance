<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Class UpdateImportSettingAddModuleHandler
 */
class UpdateImportSettingsAddModuleHandler extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("import_settings", function (\Illuminate\Database\Schema\Blueprint $table) {

            $table->text("module_handler")->nullable()->comment("导入任务处理类");
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("import_settings", function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn("module_handler");
        });
    }
}
