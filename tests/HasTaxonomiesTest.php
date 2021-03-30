<?php

namespace Devio\Taxonomies\Tests;

use Devio\Taxonomies\Term;
use Devio\Taxonomies\Tests\Support\Post;

class HasTaxonomiesTest extends TestCase
{
    /** @test */
    public function it_can_attach_a_term()
    {
        $post = Post::factory()->create();
        $post->attachTerm('foo');

        $this->assertEquals('foo', $post->terms[0]->name);
    }

    /** @test */
    public function it_can_attach_a_term_from_specific_taxonomy() {
        $post = Post::factory()->create();
        $post->attachTerm('foo', 'products');

        $this->assertEquals('products', $post->terms[0]->taxonomy->name);
    }

    /** @test */
    public function it_attaches_terms_without_detaching() {
        $post = Post::factory()->create();
        $post->attachTerm('foo');
        $post->attachTerm('bar');

        $this->assertCount(2, $post->terms);
    }

    /** @test */
    public function it_attaches_terms_from_array()
    {
        $post = Post::factory()->create();
        $post->attachTerm(['foo', 'bar']);

        $this->assertCount(2, $post->terms);
    }

    /** @test */
    public function it_syncs_a_given_bunch_of_terms() {
        $post = Post::factory()->create();
        $post->attachTerm(['foo', 'bar']);

        $post->syncTerms(['baz', 'qux']);

        $this->assertCount(2, $post->terms);
        $this->assertEquals('baz', $post->terms[0]->name);
        $this->assertEquals('qux', $post->terms[1]->name);

        $post->syncTerms(Term::store(['term1', 'term2']));
        $post->refresh();

        $this->assertCount(2, $post->terms);
        $this->assertEquals('term1', $post->terms[0]->name);
        $this->assertEquals('term2', $post->terms[1]->name);
    }

    /** @test */
    public function it_syncs_terms_with_taxonomy() {
        $post = Post::factory()->create();

        $post->syncTerms(['baz', 'qux'], 'category');

        $this->assertCount(2, $post->terms);
        $this->assertEquals('category', $post->terms[0]->taxonomy->name);
    }

    /** @test */
    public function it_only_syncs_terms_of_given_taxonomy() {
        $post = Post::factory()->create();
        $post->attachTerm(['foo', 'bar']);
        $post->attachTerm(['baz', 'qux'], 'category');

        $post->syncTermsOfTaxonomy(['quux', 'quuz'], 'category');

        $this->assertCount(4, $post->terms);
        $this->assertEquals('foo', $post->terms[0]->name);
        $this->assertEquals('bar', $post->terms[1]->name);
        $this->assertEquals('quux', $post->terms[2]->name);
        $this->assertEquals('quuz', $post->terms[3]->name);
    }

//    /** @test */
//    public function it_queues_terms_when_setting_terms_attribute()
//    {
//        $post = Post::factory()->create();
//
//        $post->terms = [Term::store('foo'), Term::store('bar')];
//
//        $this->assertCount(2, $post->getTermsQueue());
//    }
//
//    /** @test */
//    public function it_flushes_terms_queue_after_saving()
//    {
//        $post = Post::factory()->create();
//
//        $post->terms = [Term::store('foo'), Term::store('bar')];
//
//        $post->save();
//
//        $this->assertCount(0, $post->getTermsQueue());
//    }
}
