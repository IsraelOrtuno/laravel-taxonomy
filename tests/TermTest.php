<?php

namespace Devio\Taxonomies\Tests;

use Devio\Taxonomies\Taxonomy;
use Devio\Taxonomies\Term;

class TermTest extends TestCase
{
    /** @test */
    public function it_creates_a_term()
    {
        Term::store('foo');

        $this->assertDatabaseHas('terms', ['name' => 'foo']);
    }

    /** @test */
    public function it_creates_a_term_on_default_taxonomy()
    {
        $term = Term::store('foo');

        $this->assertEquals('default', $term->taxonomy->name);
    }

    /** @test */
    public function it_creates_taxonomy_with_configured_name_if_none_given()
    {
        config()->set('taxonomy.default_taxonomy_name', 'foo');

        $term = Term::store('foo');
        $this->assertEquals('foo', $term->taxonomy->name);
    }

    /** @test */
    public function it_creates_taxonomy_if_not_found()
    {
        Term::store('foo', 'category');

        $this->assertDatabaseHas('taxonomies', ['name' => 'category']);
    }

    /** @test */
    public function it_creates_a_term_with_string_taxonomy()
    {
        $term = Term::store('foo', 'category');
        $this->assertEquals('category', $term->taxonomy->name);
    }

    /** @test */
    public function it_creates_a_term_with_taxonomy_instance()
    {
        $taxonomy = Taxonomy::store('category');
        $term = Term::store('foo', $taxonomy);
        $this->assertEquals('category', $term->taxonomy->name);
    }

    /** @test */
    public function it_creates_a_term_with_taxonomy_id()
    {
        $taxonomy = Taxonomy::store('category');
        $term = Term::store('foo', $taxonomy->id);
        $this->assertEquals('category', $term->taxonomy->name);
    }

    /** @test */
    public function it_creates_multiple_terms()
    {
        $names = ['foo', 'bar', 'baz'];

        $terms = Term::store($names);

        $this->assertCount(3, $terms);
        $this->assertEquals('foo', $terms[0]->name);

        $terms = Term::store(collect($names));
        $this->assertCount(3, $terms);
        $this->assertEquals('foo', $terms[0]->name);
    }
}
