<?php

namespace Devio\Taxonomies\Tests\Support;

use Devio\Taxonomies\HasTaxonomies;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory, HasTaxonomies;

    public $table = 'devio_taxonomies_posts';

    protected $fillable = ['name', 'terms'];

    protected static function newFactory()
    {
        return PostFactory::new();
    }
}
