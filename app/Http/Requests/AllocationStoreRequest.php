<?php

namespace App\Http\Requests;

use App\Models\Allocation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class AllocationStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', Allocation::class);
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'institution_guid.required' => 'The Institution field is required.',
            'program_year_guid.required' => 'The Program Year field is required.',
            'required' => 'The :attribute field is required.',
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
            'guid' => 'required|unique:allocations,guid',
            'institution_guid' => 'required|exists:institutions,guid',
            'program_year_guid' => 'required|exists:program_years,guid',
            'total_amount' => 'required|numeric',
            'status' => 'required',
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
            'guid' => Str::orderedUuid()->getHex(),
            'last_touch_by_user_guid' => $this->user()->guid,
            'status' => 'active',
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
