<?php

namespace Devio\Taxonomies;

use Illuminate\Database\Eloquent\Model;

class Taxonomy extends Model
{
    public $guarded = [];

    /**
     * Create a new taxonomy record.
     *
     * @param $name
     * @return static
     */
    public static function store($name)
    {
        if ($name instanceof self) {
            return $name;
        } elseif (is_numeric($name)) {
            return (new static)->findOrFail($name);
        }

        return static::firstOrCreate(compact('name'));
    }

    /**
     * Relationship to terms table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function terms()
    {
        return $this->hasMany(Term::class);
    }
}
