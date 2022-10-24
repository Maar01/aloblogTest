<?php

namespace App\Validators;

use App\Rules\ValidCommentLvl;

class CommentValidator
{
    public function rules()
    {
        return [
            'message' => 'required|string|',
            'name' => 'required|string|max:30',
            'parent_id' => [
                'nullable', 'int', 'exists:comments,id', new ValidCommentLvl()
            ]
        ];

    }

    public function validate($data)
    {
        return validator($data, $this->rules())->validate();
    }
}
