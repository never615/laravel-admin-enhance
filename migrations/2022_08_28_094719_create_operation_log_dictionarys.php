<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOperationLogDictionarys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('operation_log_dictionarys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable()->comment('api路径名称');
            $table->string('path')->nullable()->comment('api路径');
            $table->timestamps();
            $table->index('path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('operation_log_dictionarys');
    }
}
