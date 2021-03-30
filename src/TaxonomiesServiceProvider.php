<?php

namespace Devio\Taxonomies;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class TaxonomiesServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/taxonomy.php', 'taxonomy');
        $this->loadMigrationsFrom(__DIR__ . '/../databsae/migrations');

        // Pluck model keys based on model's configured key property to make sure this
        // would work even if a user overrides the default Eloquent behaviour
        Collection::macro('pluckModelKeys', fn() => $this->map(fn($item) => $item->getKey()));
    }
}
