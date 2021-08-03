<?php

namespace Devio\Taxonomies\Tests;

use Devio\Taxonomies\MergeTerms;
use Devio\Taxonomies\Term;
use Devio\Taxonomies\Tests\Support\Post;

class MergerTest extends TestCase
{
    /** @test */
    public function it_merges_a_collection_of_terms()
    {
        $from = Term::store(['term1', 'term2', 'term3']);
        $to = Term::store('termTo');

        (new MergeTerms($from, $to))->merge();

        $this->assertDatabaseCount('terms', 1);
    }
    
    /** @test */
    public function it_replaces_taxables_relations()
    {
        $from = Term::store(['term1', 'term2', 'term3']);
        $to = Term::store('termTo');

        $post = Post::factory()->create();
        $post->attachTerms($from);

        (new MergeTerms($from, $to))->merge();

        $this->assertCount(1, $post->terms);
        $this->assertEquals('termTo', $post->terms->first()->name);
    }
}
