<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateElasticFilterTable
 */
class AddTimestampsToElasticFilterTable extends Migration
{
    public function up()
    {
        Schema::table('elastic_filters', function (Blueprint $table) {
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::table('elastic_filters', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
}
