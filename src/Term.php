<?php

namespace Devio\Taxonomies;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Term extends Model
{
    use HasTranslations;

    public $guarded = [];

    public array $translatable = ['name', 'key'];

    /**
     * Create a new term bound to a taxonomy.
     * @param Collection|array|string $terms
     * @param Taxonomy|string|null $taxonomy
     * @param string|null $locale
     * @return Collection|Term
     */
    public static function store(
        Collection|array|string $terms,
        Taxonomy|string $taxonomy = null,
        string $locale = null): Collection|self
    {
        $taxonomy = app(Taxonomy::class)->store($taxonomy);

        if (!is_array($terms) && !$terms instanceof \ArrayAccess) {
            return static::findOrCreate($terms, $taxonomy, $locale);
        }

        return collect($terms)->map(fn($term) => static::findOrCreate($term, $taxonomy, $locale));
    }

    public function entity()
    {
        return $this->morphedByMany($this->taxable_type, 'taxables');
    }

    /**
     * Resolve a single or a collection of terms that exist in database for a given taxonomy.
     * @param string|array|Collection $terms
     * @param string|Taxonomy|null $taxonomy
     * @param string|null $locale
     * @return Collection
     */
    public function resolve(array|Collection|string $terms, string|Taxonomy $taxonomy = null, string $locale = null): Collection
    {
        if (is_array($terms) || $terms instanceof Collection) {
            $terms = collect($terms);
            $instances = $terms->filter(fn ($term) => $term instanceof Collection)->count();

            // All are instances of Term, so we do not need to find anything
            if ($terms->count() === $instances) {
                return $terms;
            }
        }

        return static::getFromString($terms, $taxonomy, $locale)->filter();
    }

    /**
     * Get all terms from a string and taxonomy.
     * @param string|array $terms
     * @param string|Taxonomy|null $taxonomy
     * @param string|null $locale
     * @return Collection
     */
    public static function getFromString(array|Collection|string $terms, string|Taxonomy $taxonomy = null, string $locale = null): Collection
    {
        $locale = $locale ?? app()->getLocale();

        $taxonomy = app(Taxonomy::class)->resolve($taxonomy);

        return static::whereIn("name->{$locale}", collect($terms))
//            ->orWhereIn("key->{$locale}", collect($terms))
            ->whereHas('taxonomy', fn(Builder $query) => $query->where('id', $taxonomy->getKey()))
            ->get();
    }

    /**
     * Get a term from a string and taxonomy.
     * @param string|array $terms
     * @param string|Taxonomy|null $taxonomy
     * @param string|null $locale
     * @return Term|null
     */
    public static function findFromString(string|array $terms, string|Taxonomy $taxonomy = null, string $locale = null): self|null
    {
        return static::getFromString($terms, $taxonomy, $locale)->first();
    }

    /**
     * Get the first matching term from database.
     * @param Term|string $term
     * @param Taxonomy $taxonomy
     * @param string|null $locale
     * @return Term
     */
    protected static function findOrCreate(Term|string $term, Taxonomy $taxonomy, string $locale = null): self
    {
        $locale = $locale ?? app()->getLocale();

        if ($term instanceof Term) return $term;

        return static::findFromString($term, $taxonomy, $locale) ?? static::create([
                'name' => [$locale => $term],
                'taxonomy_id' => $taxonomy->getKey()
            ]);
    }

    /**
     * Relationship to taxonomies table.
     * @return BelongsTo
     */
    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(
            get_class(app(Taxonomy::class))
        );
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
