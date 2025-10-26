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
        $validSources = array_column(NewsSource::cases(), 'value');
        
        return [
            'q' => 'nullable|string|max:255',
            'source' => 'nullable|string', // Comma-separated sources
            'source.*' => Rule::in($validSources), // Validate each source if array
            'category' => 'nullable|string|max:100',
            'author' => 'nullable|string|max:255',
            'from' => 'nullable|date|before_or_equal:to',
            'to' => 'nullable|date|after_or_equal:from',
            'per_page' => 'nullable|integer|min:1|max:100',
            'ignore_preferences' => 'sometimes|boolean',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert ignore_preferences to boolean if it's a string
        if ($this->has('ignore_preferences')) {
            $value = $this->input('ignore_preferences');
            if (is_string($value)) {
                $this->merge([
                    'ignore_preferences' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                ]);
            }
        }
        
        // If source is a comma-separated string, validate each one
        if ($this->has('source') && is_string($this->source)) {
            $sources = array_filter(array_map('trim', explode(',', $this->source)));
            $validSources = array_column(NewsSource::cases(), 'value');
            
            foreach ($sources as $source) {
                if (!in_array($source, $validSources)) {
                    $this->merge(['source' => null]); // Will fail validation
                    break;
                }
            }
        }
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
