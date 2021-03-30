<?php

namespace Devio\Taxonomies\Tests\Support;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestsSchema extends Migration
{
    public function up()
    {
        Schema::create('devio_taxonomies_posts', function (Blueprint $table) {
            $table->id('id');
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('devio_taxonomy_posts');
    }
}
