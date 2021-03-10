<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSubjectSettingsAddFrontColumn extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subject_settings', function (Blueprint $table) {
            $table->jsonb('front_column')->nullable()->comment('前端可以请求的列');
            $table->jsonb('file_type_column')->nullable()->comment('文件类型的列');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subject_settings', function (Blueprint $table) {
            $table->dropColumn('front_column');
            $table->dropColumn('file_type_column');
        });
    }
}
