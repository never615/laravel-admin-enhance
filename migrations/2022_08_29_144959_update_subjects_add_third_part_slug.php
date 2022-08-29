<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 添加主体第三方项目标识
 */
class UpdateSubjectsAddThirdPartSlug extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('subjects', 'third_part_mall_id')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->string("third_part_mall_id")->nullable()->comment('第三方项目标识');
            });
        }
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('third_part_mall_id');
        });
    }
}
