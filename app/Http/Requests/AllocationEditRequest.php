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
            'ts_percent' => 'required|numeric|min:0|max:100',
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
