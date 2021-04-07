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
        Schema::create('elastic_filters', function (Blueprint $table) {
            $table->increments('id');
            $table->string('category')->comment('category or needed part of url');
            $table->string('index')->comment('Elastic Index name');
            $table->string('slug')->comment('Field`s name in elastic');
            $table->string('url_slug')->comment('Other url sug for SEO)')->nullable();
            $table->char('type', 12)->comment('Type for filter viewing')->nullable();
            $table->string('title')->comment('Title for field on the screen');
            $table->integer('sort')->comment('Order for field on the screen')->default(100);
            $table->string('unit')->comment('Unit or phrase, after title or field')->nullable();
            $table->string('hint')->comment('Hint for filter on the screen')->nullable();

            $table->unique(['category','index','slug'], 'unique_category_index_slug');
        });
    }

    public function down()
    {
        Schema::drop('elastic_filters');
    }
}
