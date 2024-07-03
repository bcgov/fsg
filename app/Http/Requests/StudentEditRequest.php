<?php

namespace App\Http\Requests;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StudentEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $student = Student::find($this->id);

        return $this->user()->can('update', $student);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {


        return [
            'id' => 'required',
            'guid' => 'required',
            'user_guid' => 'required|exists:users,guid',
            'sin' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'dob' => 'required|date_format:Y-m-d',
            'email' => 'required|email',
            'city' => 'nullable',
            'zip_code' => 'nullable',

            'citizenship' => 'required',
            'grade12_or_over19' => 'required',

            'info_consent' => 'boolean',
            'duplicative_funding' => 'boolean',
            'tax_implications' => 'boolean',
            'lifetime_max' => 'boolean',
            'fed_prov_benefits'  => 'boolean',
            'workbc_client' => 'boolean',
            'additional_supports' => 'boolean',
            'bc_resident' => 'boolean',
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
            'email' => Str::lower(str_replace(' ', '', $this->email)),
            'zip_code' => Str::upper(str_replace(' ', '', $this->zip_code)),
            'city' => Str::title(str_replace(' ', '', $this->city)),
            'first_name' => Str::title(str_replace(' ', '', $this->first_name)),
            'last_name' => Str::title(str_replace(' ', '', $this->last_name)),

            'info_consent' => $this->toBoolean($this->info_consent),
            'duplicative_funding' => $this->toBoolean($this->duplicative_funding),
            'tax_implications' => $this->toBoolean($this->tax_implications),
            'lifetime_max' => $this->toBoolean($this->lifetime_max),
            'fed_prov_benefits'  => $this->toBoolean($this->fed_prov_benefits),
            'workbc_client' => $this->toBoolean($this->workbc_client),
            'additional_supports' => $this->toBoolean($this->additional_supports),
            'bc_resident' => $this->toBoolean($this->bc_resident),

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
