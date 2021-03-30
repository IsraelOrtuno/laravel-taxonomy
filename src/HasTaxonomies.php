<?php

namespace Devio\Taxonomies;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasTaxonomies
{
    protected $termsQueue = [];

    public static function bootHasTaxonomies()
    {
        static::saved(function (self $taxableModel) {
            if (!count($queue = $taxableModel->getTermsQueue())) {
                return;
            }

            foreach ($queue as $term) {
                $term = app(Term::class)->store($term);
            }

            $taxableModel->flushTermsQueue();
        });
    }

    public function terms(): MorphToMany
    {
        return $this->morphToMany($this->getTermsClass(), 'taxable');
    }

    public function attachTerm($term, $taxonomy = null)
    {
        $terms = Term::store(collect($term), $taxonomy);

        $this->terms()->syncWithoutDetaching(
            $terms->pluckModelKeys()
        );

        return $this;
    }

    public function syncTerms($terms, $taxonomy = null)
    {
        $terms = Term::store(collect($terms), $taxonomy);

        $this->terms()->sync($terms->pluckModelKeys());

        return $this;
    }

    public function syncTermsOfTaxonomy($terms, $taxonomy = null)
    {
        $terms = Term::store($terms, $taxonomy = Taxonomy::store($taxonomy));

        $termsToDetach = $this->terms()->where(
        // taxonomy_id == $taxonomy->id
            app(Term::class)->getTaxonomyForeignKey(), $taxonomy->getKey()
        )->get();

        $this->terms()->detach(
            $termsToDetach->pluckModelKeys()
        );

        $this->terms()->syncWithoutDetaching(
            $terms->pluckModelKeys()
        );

        return $this;
    }

    public function setTermsAttribute($terms)
    {
        $this->termsQueue = collect($terms);
    }

    public function scopeWhereTerm(Builder $query, $term = null)
    {
    }

    public function scopeWhereAnyTerm()
    {
    }

    /**
     * @return array
     */
    public function getTermsQueue(): array
    {
        return $this->termsQueue ?? [];
    }

    public function flushTermsQueue(): void
    {
        $this->termsQueue = [];
    }

    public function getTermsClass()
    {
        return get_class(app(Term::class));
    }
}
