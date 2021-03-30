<?php

namespace Devio\Taxonomies;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

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
        if (!$taxonomy) {
            $taxonomy = config('taxonomy.default_taxonomy_name');
        }

        $taxonomy = app(Taxonomy::class)->store($taxonomy);

        if (!is_array($terms) && !$terms instanceof \ArrayAccess) {
            return static::findOrCreate($terms, $taxonomy);
        }

        return collect($terms)->map(fn($term) => static::findOrCreate($term, $taxonomy));
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

    /**
     * Get the foreign key for the taxonomy relationship.
     */
    public function getTaxonomyForeignKey()
    {
        return (new static)->taxonomy()->getForeignKeyName();
    }
}
