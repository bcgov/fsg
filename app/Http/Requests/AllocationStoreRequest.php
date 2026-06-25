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
            'funding_types.*.funding_type.required' => 'The Funding Type field is required.',
            'funding_types.*.funding_type.exists' => 'The selected Funding Type is invalid.',
            'funding_types.*.amount.required' => 'The Funding Type amount is required.',
            'funding_types.*.amount.lte' => 'The Funding Type amount cannot be higher than the Total Allowed.',
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
            'funding_types' => 'nullable|array',
            'funding_types.*.funding_type' => 'required|string|exists:utils,field_name',
            'funding_types.*.amount' => 'required|numeric|min:0|lte:total_amount',
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
            'status' => 'new',
        ]);
    }
}
