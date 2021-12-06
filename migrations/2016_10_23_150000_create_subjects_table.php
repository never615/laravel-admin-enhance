<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 项目主体
 * Class CreateSubjectsTable
 */
class CreateSubjectsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique()->comment('主体名字');
            $table->unsignedInteger("parent_id")->nullable()->comment("父id");
            $table->string('logo')->nullable()->comment('logo');
            $table->text('description')->nullable()->comment('描述');
            $table->string("uuid")->nullable()
                ->comment("商城的唯一标识,MT所有的主体的包括不同项目均拥有唯一一个uuir");

            $table->timestamps();
            $table->softDeletes();
            $table->unique("uuid");
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subjects');
    }
}
