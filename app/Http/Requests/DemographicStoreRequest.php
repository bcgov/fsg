<?php

namespace App\Http\Requests;

use App\Models\Demographic;
use Illuminate\Foundation\Http\FormRequest;

class DemographicStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', Demographic::class);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'question.*' => 'Question is not valid.',
            'type.*' => 'Type is not valid.',
            'required.*' => 'Required field is not valid.',
            'active.*' => 'Active field is not valid.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'question' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:text,select,multi-select,radio,checkbox',
            'required' => 'required|boolean',
            'active' => 'required|boolean',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'required' => $this->toBoolean($this->required),
            'active' => $this->toBoolean($this->active),
        ]);
    }

    /**
     * Convert to boolean
     *
     * @return bool
     */
    private function toBoolean($booleable)
    {
        return filter_var($booleable, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
