<?php

namespace Devio\Taxonomies;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Term extends Model
{
    public $guarded = [];

    /**
     * Create a new term bound to a taxonomy.
     * @param Collection|array|string $terms
     * @param Taxonomy|string|null $taxonomy
     * @return Collection|Term
     */
    public static function store(Collection|array|string $terms, Taxonomy|string $taxonomy = null): Collection|Term
    {
        $taxonomy = app(Taxonomy::class)->store(
            static::resolveTaxonomyName($taxonomy)
        );

        if (!is_array($terms) && !$terms instanceof \ArrayAccess) {
            return static::findOrCreate($terms, $taxonomy);
        }

        return collect($terms)->map(fn($term) => static::findOrCreate($term, $taxonomy));
    }

    /**
     * Get all terms from a string and taxonomy.
     * @param string|array $terms
     * @param string|null $taxonomy
     * @return Collection
     */
    public static function getFromString(string|array $terms, string $taxonomy = null): Collection
    {
        $taxonomy = static::resolveTaxonomyName($taxonomy);

        return static::whereIn('name', collect($terms))
            ->whereHas('taxonomy', fn(Builder $query) => $query->where('name', $taxonomy))
            ->get();
    }

    /**
     * Get a term from a string and taxonomy.
     * @param string|array $terms
     * @param string|null $taxonomy
     * @return Term
     */
    public static function findFromString(string|array $terms, string $taxonomy = null): Term
    {
        return static::getFromString($terms, $taxonomy)->first();
    }

    /**
     * Get the first matching term from database.
     * @param $term
     * @param $taxonomy
     * @return Term
     */
    protected static function findOrCreate(Term|string $term, Taxonomy $taxonomy): Term
    {
        return $term instanceof Term ? $term : static::firstOrCreate([
            'name' => $term,
            'taxonomy_id' => $taxonomy->getKey()
        ]);
    }

    /**
     * Relationship to taxonomies table.
     * @return BelongsTo
     */
    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class);
    }

    /**
     * Get the given taxonomy name or default from config.
     * @param null $taxonomy
     * @return string
     */
    protected static function resolveTaxonomyName(Taxonomy|string $taxonomy = null): string
    {
        return $taxonomy instanceof Taxonomy ? $taxonomy->name : $taxonomy ?? config('taxonomy.default_taxonomy_name');
    }

    /**
     * Get the foreign key for the taxonomy relationship.
     * @return string
     */
    public function getTaxonomyForeignKey(): string
    {
        return (new static)->taxonomy()->getForeignKeyName();
    }
}
