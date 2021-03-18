<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

use Illuminate\Database\Migrations\Migration;

class UpdateSubjectsJsonb extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement('ALTER TABLE subjects
  ALTER COLUMN extra_config
  SET DATA TYPE jsonb
  USING extra_config::jsonb;');

        \DB::statement('ALTER TABLE subjects
  ALTER COLUMN open_extra_config
  SET DATA TYPE jsonb
  USING open_extra_config::jsonb;');

    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
