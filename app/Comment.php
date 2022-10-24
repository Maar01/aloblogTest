<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Comment extends Model
{
    public const LEAF = 3;
    public const ROOT = 1;

    protected $fillable = ['message', 'name', 'parent_id', 'level'];

    public static function getById(int $id): self
    {
        $comment = self::baseQuery()->where('id', $id)->first();

        return new self((array)$comment);
    }

    public function store(array $properties): self
    {
        $id = self::baseQuery()->insertGetId([
            'name' => $properties['name'],
            'level' => $properties['level'],
            'message' => $properties['message'],
            'parent_id' => $properties['parent_id'],
            'created_at' => now(),
        ]);

        return self::getById($id);
    }

    public function updateWith(array $newValues)
    {
        self::baseQuery()->where('id', $this->id)->update($newValues);
        return self::getById($this->id);
    }

    public function selfDestroy(): void
    {
        self::baseQuery()->where('id', $this->id)->delete();
    }

    public function getParent(): self
    {
        return self::baseQuery()->where('parent_id', $this->id)->first();
    }

    /*private function getRoot()
    {
       $root = $this->parent_id ? $this->getParent() : $this;
       if ($root) {
           $root = $root->getRoot();
       }

       return $root;
    }*/

    public function isLeaf(): bool
    {
        return $this->level === self::LEAF;
    }

    public static function baseQuery()
    {
        return DB::table('comments');
    }
}
