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
     * @return static
     */
    public static function store($name): static
    {
        if ($name instanceof self) {
            return $name;
        } elseif (is_numeric($name)) {
            return (new static)->findOrFail($name);
        }

        return static::firstOrCreate(compact('name'));
    }

    /**
     * Find a taxonomy by name.
     * @param string $name
     * @return static
     */
    public static function fromString(string $name): static
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
