<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateElasticFilterTable
 */
class CreateElasticFilterTable extends Migration
{
    public function up()
    {
        Schema::create('elastic_filter', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url');
            $table->string('title');
            $table->char('type', 12)->comment('Type for filter viewing');
            $table->string('index')->comment('Elastic Index name');
            $table->string('slug')->comment('Field`s name in elastic');
            $table->string('url_slug')->comment('Url slug name')->nullable();
            $table->integer('sort')->comment('Field for sorting')->default(100);
            $table->integer('step')->comment('Step for range type')->nullable();
            $table->string('unit')->comment('Unit or phrase, after title or field')->nullable();
            $table->string('hint')->comment('Hint for filter')->nullable();
        });
    }

    public function down()
    {
        Schema::drop('elastic_filter');
    }
}
