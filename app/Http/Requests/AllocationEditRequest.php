<?php

namespace App\Http\Requests;

use App\Models\Allocation;
use Illuminate\Foundation\Http\FormRequest;

class AllocationEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $allocation = Allocation::find($this->id);

        // Only allow editing active allocations
        if ($allocation->status == 'inactive') {
            return false;
        }

        // Check if the authenticated user has the necessary permissions to edit the institution.
        // You can access the authenticated user using the Auth facade or $this->user() method.
        return $this->user()->can('update', $allocation);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'funding_types.*.funding_type.required' => 'The Funding Type field is required.',
            'funding_types.*.funding_type.exists' => 'The selected Funding Type is invalid.',
            'funding_types.*.amount.required' => 'The Funding Type amount is required.',
            'funding_types.*.amount.lte' => 'The Funding Type amount cannot be higher than the Total Allowed.',
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
            'id' => 'required',
            'guid' => 'required',
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
        ]);
    }

}
