<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePreferenceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by auth middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'preferred_sources' => 'nullable|array',
            'preferred_sources.*' => 'string|in:newsapi,guardian,nytimes',
            'preferred_categories' => 'nullable|array',
            'preferred_categories.*' => 'string|max:255',
            'preferred_authors' => 'nullable|array',
            'preferred_authors.*' => 'string|max:255',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'preferred_sources.*.in' => 'Each source must be one of: newsapi, guardian, nytimes',
            'preferred_categories.*.string' => 'Each category must be a string',
            'preferred_authors.*.string' => 'Each author must be a string',
            'keywords.*.string' => 'Each keyword must be a string',
        ];
    }
}

