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
class CreateImportRecordsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("import_records", function (\Illuminate\Database\Schema\Blueprint $table) {

            $table->increments('id');
            $table->unsignedInteger('subject_id');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('CASCADE');

            $table->string("module_slug")
                ->comment("导入模块的标识,通过依赖注入该标识,获得对应实例");

            $table->string("status")
                ->default("not_start")
                ->comment("导入任务的状态");

            $table->string("file_url")->nullable()->comment("导入文件的地址");

            $table->text("failure_reason")
                ->nullable()
                ->comment("失败原因");

            $table->timestamp("finish_at")
                ->nullable();

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
        Schema::dropIfExists('import_records');
    }
}
