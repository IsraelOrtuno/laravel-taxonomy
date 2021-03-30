<?php

namespace Devio\Taxonomies;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    public $guarded = [];

    /**
     * Create a new term bound to a taxonomy.
     *
     * @param $term
     * @param string $taxonomy
     * @return \Illuminate\Support\Collection|static
     */
    public static function store($terms, $taxonomy = null)
    {
        $taxonomy = static::resolveTaxonomyName($taxonomy);

        $taxonomy = app(Taxonomy::class)->store($taxonomy);

        if (!is_array($terms) && !$terms instanceof \ArrayAccess) {
            return static::findOrCreate($terms, $taxonomy);
        }

        return collect($terms)->map(fn($term) => static::findOrCreate($term, $taxonomy));
    }

    public static function search($terms, $taxonomy = null)
    {
        return collect($terms)->map(function ($value) {

        });
    }

    public static function fromString($term, $taxonomy = null)
    {
        $taxonomy = static::resolveTaxonomyName($taxonomy);

        return static::where('name', $term)
            ->whereHas('taxonomy', fn(Builder $query) => $query->where('name', $taxonomy))
            ->first();
    }

    protected static function findOrCreate($term, $taxonomy)
    {
        return $term instanceof Term ? $term : static::firstOrCreate([
            'name' => $term,
            'taxonomy_id' => $taxonomy->getKey()
        ]);
    }

    /**
     * Relationship to taxonomies table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function taxonomy()
    {
        return $this->belongsTo(Taxonomy::class);
    }

    protected static function resolveTaxonomyName($taxonomy = null)
    {
        return $taxonomy ?? config('taxonomy.default_taxonomy_name');
    }

    /**
     * Get the foreign key for the taxonomy relationship.
     */
    public function getTaxonomyForeignKey()
    {
        return (new static)->taxonomy()->getForeignKeyName();
    }
}
