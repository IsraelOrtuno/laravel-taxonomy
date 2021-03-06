<?php

namespace Devio\Taxonomies\Tests;

use Devio\Taxonomies\Taxonomy;
use Devio\Taxonomies\Term;
use Devio\Taxonomies\Tests\Support\Post;

class HasTaxonomiesTest extends TestCase
{
    /** @test */
    public function it_can_attach_a_term()
    {
        $post = Post::factory()->create();
        $post->attachTerms('foo');

        $this->assertEquals('foo', $post->terms[0]->name);
    }

    /** @test */
    public function it_can_attach_a_localized_term()
    {
        $post = Post::factory()->create();
        $post->attachTerms('foo', null, 'es');

        $this->assertEquals('foo', $post->terms[0]->getTranslation('name', 'es'));
    }

    /** @test */
    public function it_can_attach_a_term_from_specific_taxonomy()
    {
        $post = Post::factory()->create();
        $post->attachTerms('foo', 'products');

        $this->assertEquals('products', $post->terms[0]->taxonomy->name);
    }

    /** @test */
    public function it_attaches_terms_without_detaching()
    {
        $post = Post::factory()->create();
        $post->attachTerms('foo');
        $post->attachTerms('bar');

        $this->assertCount(2, $post->terms);
    }

    /** @test */
    public function it_attaches_terms_from_array()
    {
        $post = Post::factory()->create();
        $post->attachTerms(['foo', 'bar']);

        $this->assertCount(2, $post->terms);
    }

    /** @test */
    public function it_detaches_a_given_term()
    {
        $post = Post::factory()->create();
        $post->attachTerms(['foo', 'bar']);

        $post->detachTerms('foo');

        $this->assertCount(1, $post->terms);
    }

    /** @test */
    public function it_detaches_multiple_terms_at_once()
    {
        $post = Post::factory()->create();
        $post->attachTerms(['foo', 'bar']);

        $post->detachTerms(['foo', 'bar']);

        $this->assertCount(0, $post->terms);
    }

    /** @test */
    public function it_detaches_terms_from_a_specific_taxonomy()
    {
        $post = Post::factory()->create();
        $post->attachTerms(['foo', 'bar'], 'category');
        $post->attachTerms(['foo', 'bar'], 'product');

        $post->detachTerms('foo', 'category');

        $this->assertCount(3, $post->terms);
        $this->assertEquals('bar', $post->terms[0]->name);
        $this->assertEquals('foo', $post->terms[1]->name);
        $this->assertEquals('bar', $post->terms[2]->name);
    }

    /** @test */
    public function it_detaches_all_terms_in_once()
    {
        $post = Post::factory()->create()
            ->attachTerms(['foo', 'bar']);

        $post->detachAllTerms();

        $this->assertCount(0, $post->terms);
    }

    /** @test */
    public function it_detaches_all_terms_for_a_given_taxonomy()
    {
        $post = Post::factory()->create()
            ->attachTerms(['foo', 'bar'])
            ->attachTerms(['baz', 'qux'], 'category');

        $post->detachAllTermsForTaxonomy('category');

        $this->assertCount(2, $post->terms);
        $this->assertEquals('foo', $post->terms[0]->name);
    }

    /** @test */
    public function it_detaches_terms_on_entity_deletion()
    {
        $post = Post::factory()->create()
            ->attachTerms(['foo', 'bar'])
            ->attachTerms(['foo', 'bar'], 'category');

        $post->delete();

        $this->assertCount(0, $post->terms);
    }

    /** @test */
    public function it_syncs_a_given_bunch_of_terms()
    {
        $post = Post::factory()->create();
        $post->attachTerms(['foo', 'bar']);

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
    public function it_syncs_terms_with_taxonomy()
    {
        $post = Post::factory()->create();

        $post->syncTerms(['baz', 'qux'], 'category');

        $this->assertCount(2, $post->terms);
        $this->assertEquals('category', $post->terms[0]->taxonomy->name);
    }

    /** @test */
    public function it_only_syncs_terms_of_given_taxonomy()
    {
        $post = Post::factory()->create();
        $post->attachTerms(['foo', 'bar']);
        $post->attachTerms(['baz', 'qux'], 'category');

        $post->syncTermsOfTaxonomy(['quux', 'quuz'], 'category');

        $this->assertCount(4, $post->terms);
        $this->assertEquals('foo', $post->terms[0]->name);
        $this->assertEquals('bar', $post->terms[1]->name);
        $this->assertEquals('quux', $post->terms[2]->name);
        $this->assertEquals('quuz', $post->terms[3]->name);
    }

    /** @test */
    public function it_queues_terms_when_using_terms_mutator()
    {
        $post = Post::factory()->create();

        $post->terms = [Term::store('foo'), Term::store('bar')];

        $this->assertCount(2, $post->getTermsQueue());
    }

    /** @test */
    public function it_attaches_a_term_when_using_terms_mutator()
    {
        $post = Post::factory()->create();
        $post->terms = [Term::store('foo'), Term::store('bar')];
        $post->save();

        $this->assertCount(2, $post->terms);
    }

    /** @test */
    public function it_saves_terms_when_setting_on_create_array()
    {
        $post = Post::factory()->create(['terms' => ['foo', 'bar']]);
        $this->assertCount(2, $post->terms);
    }

    /** @test */
    public function it_saves_terms_when_setting_on_update_array()
    {
        $post = Post::factory()->create();
        $post->update(['terms' => ['foo', 'bar']]);
        $this->assertCount(2, $post->terms);
    }

    /** @test */
    public function it_flushes_terms_queue_after_saving()
    {
        $post = Post::factory()->create();
        $post->terms = [Term::store('foo'), Term::store('bar')];
        $post->save();

        $this->assertCount(0, $post->getTermsQueue());
    }

    /** @test */
    public function it_filters_entities_with_any_of_given_terms()
    {
        Post::factory()->create(['terms' => ['foo', 'bar']]);
        Post::factory()->create(['terms' => ['foo', 'baz']]);

        $this->assertCount(1, Post::whereTerms('bar')->get());
        $this->assertCount(2, Post::whereTerms(['bar', 'baz'])->get());
        $this->assertCount(2, Post::whereTerms('foo')->get());
    }

    /** @test */
    public function it_filters_entities_with_any_of_given_terms_and_taxonomy()
    {
        Post::factory()->create()
            ->attachTerms(['foo', 'bar'], 'category')
            ->attachTerms(['foo'], 'product');
        Post::factory()->create()
            ->attachTerms(['foo', 'baz', 'qux'], 'product');

        $this->assertCount(1, Post::whereTerms('foo', 'category')->get());
        $this->assertCount(2, Post::whereTerms('foo', 'product')->get());
        $this->assertCount(0, Post::whereTerms('foo')->get());
    }

    /** @test */
    public function it_filters_entities_with_all_given_terms()
    {
        Post::factory()->create(['terms' => ['foo', 'bar']]);
        Post::factory()->create(['terms' => ['foo', 'bar']]);
        Post::factory()->create(['terms' => ['foo', 'bar', 'baz']]);

        $this->assertCount(3, Post::whereAllTerms(['foo', 'bar'])->get());
        $this->assertCount(1, Post::whereAllTerms(['foo', 'bar', 'baz'])->get());
    }

    /** @test */
    public function it_filters_entities_with_all_given_terms_and_taxonomy()
    {
        Post::factory()->create()
            ->attachTerms(['foo', 'bar'], 'category');
        Post::factory()->create()
            ->attachTerms(['foo', 'bar'], 'category');
        Post::factory()->create()
            ->attachTerms(['foo', 'qux']);

        $this->assertCount(2, Post::whereAllTerms(['foo', 'bar'], 'category')->get());
        $this->assertCount(1, Post::whereAllTerms(['foo', 'bar'])->get()); // Bar does not exist on 'default' so will be ignored!
    }

    /** @test */
    public function it_filters_entities_without_any_term_of_taxonomy()
    {
        Post::factory()->create()
            ->attachTerms(['foo', 'bar'], 'category')
            ->attachTerms(['baz', 'qux']);

        $this->assertCount(0, Post::whereAllTerms([], 'category')->get());
        $this->assertCount(0, Post::whereAllTerms([])->get());
    }
    
    /** @test */
    public function it_checks_an_entity_has_any_term()
    {
        $post = Post::factory()->create()
            ->attachTerms(['foo', 'bar'], 'category')
            ->attachTerms(['baz', 'qux']);

        $this->assertTrue($post->hasAnyTerm(['foo'], 'category'));
        $this->assertTrue($post->hasAnyTerm(['foo', 'bar'], 'category'));
        $this->assertFalse($post->hasAnyTerm(['foo']));
    }

    /** @test */
    public function it_checks_an_entity_has_all_terms()
    {
        Term::store('tag0', 'category');

        $post = Post::factory()->create()
            ->attachTerms(['tag1', 'tag2', 'tag3'], 'category')
            ->attachTerms(['tag4', 'tag5']);

        $this->assertTrue($post->hasAllTerms(['tag1', 'tag2'], 'category'));
        $this->assertTrue($post->hasAllTerms(['tag4', 'tag5']));
        $this->assertFalse($post->hasAllTerms(['tag1', 'tag0'], 'category'));
    }
}
