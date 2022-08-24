<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCampaignRequest extends FormRequest
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
        return [
            'name' => 'required|max:255',
            'subject' => 'required',
            'content' => 'required',
            'footer' => 'required|max:200',
            'background_banner' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Campaign name cannot be empty.',
            'name.max' => 'Campaign name cannot exceed 255 characters.',
            'subject.required' => 'Campaign subject cannot be empty.',
            'content.required' => 'Campaign content cannot be empty.',
            'footer.required' => 'Campaign footer cannot be empty.',
            'footer.max' => 'Campaign footer cannot exceed 255 characters.',
            'background_banner.image' => 'Must be an image.',
            'background_banner.mimes' => 'Must be the format: jpeg, png, jpg, gif.',
            'background_banner.max' => 'Campaign image cannot exceed 5MB.',
        ];
    }
}
