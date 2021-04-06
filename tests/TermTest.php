<?php

namespace Devio\Taxonomies\Tests;

use Devio\Taxonomies\Taxonomy;
use Devio\Taxonomies\Term;
use Devio\Taxonomies\Tests\Support\CustomTaxonomy;
use Devio\Taxonomies\Tests\Support\Post;

class TermTest extends TestCase
{
    /** @test */
    public function it_creates_a_term()
    {
        Term::store('foo');

        $this->assertDatabaseHas('terms', ['name->en' => 'foo']);
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
        $term = Term::store('foo', $taxonomy);
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

    /** @test */
    public function it_finds_a_collection_of_terms_by_string() {
        Term::store(['foo', 'bar', 'baz']);

        $this->assertCount(2, Term::getFromString(['foo', 'bar']));
    }

    /** @test */
    public function it_finds_a_term_by_string()
    {
        Term::store('foo');

        $this->assertEquals('foo', Term::findFromString('foo')->name);
    }

    /** @test */
    public function it_finds_a_term_by_string_and_taxonomy()
    {
        Term::store('foo', 'category');
        Term::store('foo', 'product');

        $foundTerm = Term::findFromString('foo', 'product');

        $this->assertEquals('foo', $foundTerm->name);
        $this->assertEquals('product', $foundTerm->taxonomy->name);
    }

    /** @test */
    public function it_can_override_taxonomy_class()
    {
        app()->bind(Taxonomy::class, CustomTaxonomy::class);

        $taxonomy = CustomTaxonomy::store('category');
        $term = Term::store('foo', $taxonomy);

        $this->assertInstanceOf(CustomTaxonomy::class, $term->taxonomy);
    }
}
