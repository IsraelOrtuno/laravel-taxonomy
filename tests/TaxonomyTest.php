<?php

namespace Devio\Taxonomies\Tests;

use Devio\Taxonomies\Taxonomy;

class TaxonomyTest extends TestCase
{
    /** @test */
    public function it_creates_a_new_taxonomy() {
        $taxonomy = Taxonomy::store('category');

        $this->assertDatabaseHas('taxonomies', ['name' => 'category']);
        $this->assertInstanceOf(Taxonomy::class, $taxonomy);
    }

    /** @test */
    public function it_prevents_creating_multiple_taxonomies_with_same_name() {
        Taxonomy::store('category');
        $taxonomy = Taxonomy::store('category');

        $this->assertEquals(1, $taxonomy->id);
    }

    /** @test */
    public function it_finds_a_taxonomy_by_string()
    {
        Taxonomy::store('category');

        $this->assertEquals('category', Taxonomy::fromString('category')->name);
    }
}
