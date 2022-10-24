<?php

namespace Database\Factories;

use App\Comment;
use Faker\Factory;

class CommentFactory
{
    public static function make(array $data= [])
    {
        $data = array_merge(self::baseAttributes(), $data);
        return new Comment($data);
    }

    public static function create(array $data = [])
    {
        $data = array_merge(self::baseAttributes(), $data);
        return Comment::store($data);
    }

    public static function baseAttributes(): array
    {
        $faker = Factory::create();
        return [
            'name' => $faker->name,
            'level' => 1,
            'message' => $faker->text(),
            'parent_id' => null,
            'created_at' => now()
        ];
    }
}
