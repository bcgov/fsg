<?php

namespace App\Http\Requests;

use App\Models\DemographicOption;
use Illuminate\Foundation\Http\FormRequest;

class DemographicOptionEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update', $this->route('option'));
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'demographic_id.*' => 'Demographic is not valid.',
            'label.*' => 'Label is not valid.',
            'order.*' => 'Order is not valid.',
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
            'demographic_id' => 'required|exists:demographics,id',
            'label' => 'required|string|max:255',
            'value' => 'nullable|string|max:255',
            'order' => 'required|integer|min:0',
        ];
    }
}
