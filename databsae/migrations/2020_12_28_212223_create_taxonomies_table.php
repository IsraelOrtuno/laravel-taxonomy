<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaxonomiesTable extends Migration
{
    public function up()
    {
        Schema::create('taxonomies', function (Blueprint $table) {
            $table->id('id');
            $table->string('name');
//            $table->json('label');
            $table->timestamps();
        });

        Schema::create('terms', function (Blueprint $table) {
            $table->id('id');
            $table->json('name');
            $table->foreignId('taxonomy_id');
            $table->timestamps();
        });

        Schema::create('taxables', function (Blueprint $table) {
            $table->id('id');
            $table->foreignId('term_id');
            $table->morphs('taxable');
        });
    }

    public function down()
    {
        Schema::drop('taxonomies');
        Schema::drop('terms');
        Schema::drop('taxables');
    }
}
