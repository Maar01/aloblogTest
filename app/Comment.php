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

    public function getParent(): ?self
    {
        return self::find($this->parent_id)->first();
    }

    public function getComments()
    {
        if ($this->isLeaf()) {
            return [];
        }

        return self::baseQuery()->where('parent_id', $this->id)->get()->map(function ($record) {
            return new Comment($record);
        });
    }

    public function getTreeComments()
    {
        $this->comments = $this->getComments();
        foreach ($this->comments as $comment)
        {
            $comment->getTreeComments();
        }
    }

    public function isLeaf(): bool
    {
        return $this->level === self::LEAF;
    }

    public static function getRoots()
    {
        return self::baseQuery()->where('parent_id', null)->get()->map(function ($rootComment) {
            $modelComment = new Comment($rootComment);
            $modelComment->getTreeComments();
            return $modelComment;
        });
    }

   /* private function getRoot()
    {
       $root = $this->parent_id ? $this->getParent() : $this;
       if ($root->parent_id) {
           $root = $root->getRoot();
       }

       return $root;
    }*/
}
