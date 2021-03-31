<?php

namespace Devio\Taxonomies;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

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
        if ($name instanceof static) {
            return $name;
        } elseif (is_numeric($name)) {
            return (new static)->findOrFail($name);
        }

        return static::firstOrCreate(compact('name'));
    }

    public static function resolve(self|string|array|Collection $taxonomies): Collection
    {
        return collect($taxonomies)->map(function ($taxonomy) {
            if ($taxonomy instanceof static) return $taxonomy;

            return $this->getTermsClass()::getFromString($term, $taxonomy);
        })->filter();
    }

    /**
     * Find a taxonomy by name.
     * @param string $name
     * @return self
     */
    public static function findFromString(string $name): self
    {
        return static::where('name', $name)->first();
    }

    /**
     * Relationship to terms table.
     * @return HasMany
     */
    public function terms(): HasMany
    {
        return $this->hasMany(Term::class);
    }
}
