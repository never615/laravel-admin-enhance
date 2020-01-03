<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class UpdateSubjects5Table extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("subjects", function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->bigInteger('sms_count')->default(0)->comment("短信数量");
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("subjects", function ($table) {
            $table->dropColumn('sms_count');
        });
    }
}
