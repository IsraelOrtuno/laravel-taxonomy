<?php

namespace Devio\Taxonomies;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasTaxonomies
{
    /**
     * The terms queue will be storing those terms pending to be saved.
     *
     * @var array
     */
    protected Collection|array $termsQueue = [];

    /**
     * Boot the trait.
     */
    public static function bootHasTaxonomies(): void
    {
        static::saved(function (self $model) {
            if (!count($queue = $model->getTermsQueue())) {
                return;
            }

            $model->attachTerms($queue);

            $model->flushTermsQueue();
        });

        static::deleted(function (self $model) {
            $model->detachAllTerms();
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

    /**
     * Attach the given terms.
     *
     * @param $terms
     * @param null $taxonomy
     * @return $this
     */
    public function attachTerms($terms, $taxonomy = null, string $locale = null): self
    {
        $locale = $locale ?? app()->getLocale();

        $terms = Term::store(Collection::wrap($terms), $taxonomy, $locale);

        $this->terms()->syncWithoutDetaching(
            $terms->pluckModelKeys()
        );

        return $this;
    }

    /**
     * Detach the given terms.
     *
     * @param $terms
     * @param null $taxonomy
     * @return $this
     */
    public function detachTerms($terms, $taxonomy = null): self
    {
        $terms = Collection::wrap(app(Term::class)->resolve($terms, $taxonomy));

        $this->terms()->detach($terms->pluckModelKeys());

        return $this;
    }

    /**
     * Detach all terms.
     * @return $this
     */
    public function detachAllTerms()
    {
        $this->terms()->sync([]);

        return $this;
    }

    /**
     * Detach all terms for a given taxonomy (or default).
     * @param null $taxonomy
     * @return $this
     */
    public function detachAllTermsForTaxonomy($taxonomy = null): self
    {
        $taxonomy = app(Taxonomy::class)->resolve($taxonomy);

        $this->terms()->detach($taxonomy->terms->pluckModelKeys());

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
        $terms = Term::store(Collection::wrap($terms), $taxonomy);

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
        $terms = Collection::wrap(
            app(Term::class)->store($terms, $taxonomy = Taxonomy::store($taxonomy))
        );

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

    /**
     * Get all items matching at least 1 term.
     * @param Builder $query
     * @param $terms
     * @param null $taxonomy
     * @return Builder
     */
    public function scopeWhereTerms(Builder $query, $terms, $taxonomy = null): Builder
    {
        // Get existing terms instances
        $terms = app(Term::class)->resolve($terms, $taxonomy);

        return $query->whereHas('terms', function (Builder $query) use ($terms) {
            $query->whereIn('terms.id', $terms->pluckModelKeys());
        });
    }

    /**
     * Get all items matching all terms.
     * @param Builder $query
     * @param $terms
     * @param null $taxonomy
     * @return Builder
     */
    public function scopeWhereAllTerms(Builder $query, $terms, $taxonomy = null)
    {
        $taxonomyInstance = app(Taxonomy::class)->resolve($taxonomy);
        $termsCollection = app(Term::class)->resolve($terms, $taxonomyInstance);

        // If no terms given, we will then return those which have no terms attached.
        // If counts of given and resolved terms do not match, means that some terms
        // could not be resolved and may belong to other category.
        if (!count($termsCollection)) {
            return $query->whereDoesntHave('terms');
        }

        foreach ($termsCollection as $term) {
            $query->whereHas('terms', function (Builder $query) use ($term, $taxonomyInstance) {
                $query->where('terms.id', $term->getKey())
                    ->where('terms.taxonomy_id', $taxonomyInstance->getKey());
            });
        }
    }

    public function hasTerm($term, $taxonomy = null, $locale = null): bool
    {
        return $this->hasAnyTerm([$term], $taxonomy, $locale);
    }

    public function hasAnyTerm($terms, $taxonomy = null, $locale = null): bool
    {
        $termsCollection = app(Term::class)->resolve($terms, $taxonomy, $locale);

        return (bool)count(array_intersect(
            $this->terms->pluckModelKeys()->toArray(),
            $termsCollection->pluckModelKeys()->toArray()
        ));
    }

    public function hasAllTerms($terms, $taxonomy = null, $locale = null)
    {
        $termsCollection = app(Term::class)->resolve($terms, $taxonomy, $locale);

        return $this->terms->whereIn('id', $termsCollection->pluckModelKeys())->count() === $termsCollection->count();
    }

    /**
     * Add to terms queue any terms added via the ->terms mutator.
     *
     * @param $terms
     */
    public function setTermsAttribute($terms): void
    {
        $this->termsQueue = collect($terms);
    }

    /**
     * Get the terms queue.
     *
     * @return Collection|array
     */
    public function getTermsQueue(): Collection|array
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
     * @return string
     */
    public function getTermsClass(): string
    {
        return get_class(app(Term::class));
    }
}
