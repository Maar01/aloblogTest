<?php

namespace App\Http\Requests;

use App\Rules\ValidCommentLvl;
use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->isUpdate()) {
            return [
                'message' => 'sometimes|string|',
                'name' => 'sometimes|string',
            ];
        }

        return [
            'message' => 'required|string|',
            'name' => 'required|string|max:30',
            'parent_id' => [
                'nullable', 'int', 'exists:comments,id', new ValidCommentLvl()
            ]
        ];
    }

    private function isUpdate(): bool
    {
        return in_array($this->method(), ['PUT', 'PATCH']);
    }
}
