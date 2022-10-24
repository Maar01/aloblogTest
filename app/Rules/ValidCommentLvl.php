<?php

namespace App\Rules;

use App\Comment;
use Illuminate\Contracts\Validation\Rule;

class ValidCommentLvl implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $parent = Comment::find($value);
        return !$parent || $parent && $parent->isLeaf();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'This comment does not accept comments';
    }
}
