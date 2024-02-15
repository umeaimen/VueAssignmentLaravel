<?php

namespace App\Http\Requests\API\feedback;

use Illuminate\Foundation\Http\FormRequest;

class FeedbackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
        return [
            'title' => 'required|string',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'attachment' => [  'nullable',
            'mimes:jpeg,png,jpg',],
        ];
    }
   
}
