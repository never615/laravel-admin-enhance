<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * 导入文件配置
 * Class CreateImportTemplatesTable
 */
class CreateImportSettingsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("import_settings", function (\Illuminate\Database\Schema\Blueprint $table) {

            $table->increments('id');

            $table->string("name")->nullable()->comment("模块说明");

            $table->string("module_slug")
                ->comment("导入模块的标识,通过依赖注入该标识,获得对应实例");

            $table->string("template_url")
                ->nullable()->comment("导入示例模板地址");

            $table->string("template_with_annotation_url")
                ->nullable()->comment("导入示例模板带说明版本");

            $table->unique("module_slug");

            $table->timestamps();
            $table->softDeletes();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('import_settings');
    }
}
