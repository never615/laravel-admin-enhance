<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 报表
 * Class CreateReportsTable
 */
class CreateReportsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {

            $table->increments("id");

            $table->integer('subject_id')->comment('主体id');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('CASCADE');

            $table->boolean("finish")->default(false)->comment("是否完成");
            $table->string("name")->comment("报表的名字");
            $table->string("status")->nullable()->comment("报表状态");
            $table->string("path")->nullable()->comment("报表的下载地址");
            $table->text("desc")->nullable()->comment("报表的描述");

            $table->unsignedInteger("admin_user_id");
            $table->foreign('admin_user_id')->references('id')->on('admin_users');

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports');
    }
}
