<?php

namespace App;


class Comment extends WeakModel
{
    public const LEAF = 3;
    public const ROOT = 1;

    protected $table = 'comments';

    public function updateWith(array $newValues)
    {
        self::baseQuery()->where('id', $this->id)->update($newValues);
        return self::find($this->id);
    }

    public function selfDestroy(): void
    {
        self::baseQuery()->where('id', $this->id)->delete();
    }

    public function getParent(): self
    {
        return self::baseQuery()->where('parent_id', $this->id)->first();
    }

    public function isLeaf(): bool
    {
        return $this->level === self::LEAF;
    }

    /*private function getRoot()
    {
       $root = $this->parent_id ? $this->getParent() : $this;
       if ($root) {
           $root = $root->getRoot();
       }

       return $root;
    }*/


    /*public static function baseQuery()
    {
        return DB::table('comments');
    }*/
}
