<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RenameThirdPartMallIdToThirdIdInSubjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 检查 third_part_mall_id 字段是否存在
        $checkColumnExists = DB::select("
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_name = 'subjects' 
            AND column_name = 'third_part_mall_id'
        ");
        
        if (!empty($checkColumnExists)) {
            // 在 PostgreSQL 中直接重命名字段
            DB::statement('ALTER TABLE subjects RENAME COLUMN third_part_mall_id TO third_id');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 检查 third_id 字段是否存在
        $checkColumnExists = DB::select("
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_name = 'subjects' 
            AND column_name = 'third_id'
        ");
        
        if (!empty($checkColumnExists)) {
            // 在 PostgreSQL 中将字段名改回
            DB::statement('ALTER TABLE subjects RENAME COLUMN third_id TO third_part_mall_id');
        }
    }
}