<?php

namespace Devio\Taxonomies\Tests;

use Devio\Taxonomies\TaxonomiesServiceProvider;
use Devio\Taxonomies\Tests\Support\CreateTestsSchema;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Spatie\LaravelRay\RayServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        (new CreateTestsSchema)->up();
    }

    protected function getPackageProviders($app)
    {
        return [
            RayServiceProvider::class,
            TaxonomiesServiceProvider::class
        ];
    }
}
