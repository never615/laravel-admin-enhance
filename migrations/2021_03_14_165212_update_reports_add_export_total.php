<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateReportsAddExportTotal extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->unsignedBigInteger('export_total')->nullable()->default(0)->comment('当前报表导出总数');
            $table->unsignedBigInteger('now_total')->nullable()->default(0)->comment('当前报表已导出数量');
            $table->decimal('now_percentage', 10, 2)->nullable()->default(0)->comment('当前报表导出进度');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('export_total');
            $table->dropColumn('now_total');
            $table->dropColumn('now_percentage');
        });
    }
}
