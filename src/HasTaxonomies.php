<?php

namespace Devio\Taxonomies;

use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasTaxonomies
{
    /**
     * The terms queue will be storing those terms pending to be saved.
     *
     * @var array
     */
    protected $termsQueue = [];

    /**
     * Boot the trait.
     */
    public static function bootHasTaxonomies()
    {
        static::saved(function (self $taxableModel) {
            if (!count($queue = $taxableModel->getTermsQueue())) {
                return;
            }

            $taxableModel->attachTerms($queue);

            $taxableModel->flushTermsQueue();
        });
    }

    /**
     * Relationship to terms table.
     *
     * @return MorphToMany
     */
    public function terms(): MorphToMany
    {
        return $this->morphToMany($this->getTermsClass(), 'taxable');
    }

    public function attachTerms($terms, $taxonomy = null): self
    {
        $terms = Term::store(collect($terms), $taxonomy);

        $this->terms()->syncWithoutDetaching(
            $terms->pluckModelKeys()
        );

        return $this;
    }

    public function detachTerms($terms, $taxonomy = null): self
    {
        $terms = $this->resolveTerms($terms, $taxonomy);

        $this->terms()->detach($terms->pluckModelKeys());

        return $this;
    }

    /**
     * Sync the given terms.
     *
     * @param $terms
     * @param null $taxonomy
     * @return $this
     */
    public function syncTerms($terms, $taxonomy = null): self
    {
        $terms = Term::store(collect($terms), $taxonomy);

        $this->terms()->sync($terms->pluckModelKeys());

        return $this;
    }

    /**
     * Sync terms of a specific taxonomy.
     *
     * @param $terms
     * @param null $taxonomy
     * @return $this
     */
    public function syncTermsOfTaxonomy($terms, $taxonomy = null): self
    {
        $terms = Term::store($terms, $taxonomy = Taxonomy::store($taxonomy));

        // taxonomy_id == $taxonomy->id
        $termsToDetach = $this->terms()->where(
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

    /**
     * Get the terms queue.
     *
     * @return array
     */
    public function getTermsQueue()
    {
        return $this->termsQueue ?? [];
    }

    /**
     * Flush the terms queue.
     */
    public function flushTermsQueue(): void
    {
        $this->termsQueue = [];
    }

    /**
     * Get the terms class name.
     *
     * @return false|string
     */
    public function getTermsClass()
    {
        return get_class(app(Term::class));
    }

    /**
     * Get an array of terms (or single) and return only existing instances.
     *
     * @param $terms
     * @param null $taxonomy
     * @return \Illuminate\Support\Collection
     */
    protected function resolveTerms($terms, $taxonomy = null)
    {
        return collect($terms)->map(function ($term) use ($taxonomy) {
            if ($term instanceof Term) return $term;

            return $this->getTermsClass()::fromString($term, $taxonomy);
        })->filter();
    }
}
