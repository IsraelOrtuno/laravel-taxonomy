<?php

namespace Devio\Taxonomies\Tests\Support;

use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
        ];
    }
}
