<?php

namespace Devio\Taxonomies;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Taxonomy extends Model
{
    public $guarded = [];

    /**
     * Create a new taxonomy record.
     * @param $name
     * @return self
     */
    public static function store($name): self
    {
        return static::resolve($name) ?? static::firstOrCreate(compact('name'));
    }

    /**
     * Resolve a taxonomy record.
     * @param Taxonomy|string $taxonomy
     * @return static
     */
    public static function resolve(self|string|null $taxonomy): self|null
    {
        // If no taxonomy is provided, we will have a resolution fallback to the default taxonomy instance.
        // If it does not exist yet, we will create now. This will happens only once.
        if (!$taxonomy) return static::store(config('taxonomy.default_taxonomy_name'));

        return $taxonomy instanceof Taxonomy ? $taxonomy : static::findFromString($taxonomy);
    }

    /**
     * Find a taxonomy by name.
     * @param string $name
     * @return self
     */
    public static function findFromString(string $name): self|null
    {
        return static::where('name', $name)->first();
    }

    /**
     * Relationship to terms table.
     * @return HasMany
     */
    public function terms(): HasMany
    {
        return $this->hasMany(
            get_class(app(Term::class))
        );
    }
}
