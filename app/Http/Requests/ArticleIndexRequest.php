<?php

namespace App\Http\Requests;

use App\Enums\NewsSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q' => 'nullable|string|max:255',
            'source' => ['nullable', 'string', Rule::in(array_column(NewsSource::cases(), 'value'))],
            'category' => 'nullable|string|max:100',
            'author' => 'nullable|string|max:255',
            'from' => 'nullable|date|before_or_equal:to',
            'to' => 'nullable|date|after_or_equal:from',
            'per_page' => 'nullable|integer|min:1|max:100',
            'ignore_preferences' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'q' => 'search keyword',
            'from' => 'start date',
            'to' => 'end date',
            'per_page' => 'items per page',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'from.before_or_equal' => 'The start date must be before or equal to the end date.',
            'to.after_or_equal' => 'The end date must be after or equal to the start date.',
            'per_page.max' => 'You can request a maximum of 100 items per page.',
        ];
    }
}
