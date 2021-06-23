<?php

namespace Devio\Taxonomies;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Taxonomy extends Model
{
    use HasTranslations;

    public $guarded = [];

//    public array $translatable = ['label'];

    /**
     * Create a new taxonomy record.
     * @param $name
     * @param string|null $label
     * @return self
     */
    public static function store($name, string $label = null): self
    {
        return static::resolve($name)
            ?? static::where('name', $name)->first();
//            ?? static::create(compact('name', 'label'));
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
