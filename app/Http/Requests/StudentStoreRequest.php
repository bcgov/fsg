<?php

namespace App\Http\Requests;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class StudentStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Student::class);
    }

    public function messages()
    {
        return [
            'first_name.required' => 'The First Name field is required.',
            'last_name.required' => 'The Last Name field is required.',
            'sin.required' => 'The SIN field is required.',
            'sin.digits' => 'The SIN must be exactly 9 digits.',
            'email.required' => 'The Email field is required.',
            'dob.required' => 'The Birth Date field is required.',
            'dob.date_format' => 'The Birth Date field is invalid.',
        ];
    }

    public function attributes()
    {
        return [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'sin' => 'SIN',
            'dob' => 'Birth Date',
            'city' => 'City',
            'zip_code' => 'Postal Code',
            'citizenship' => 'Citizenship',
            'grade12_or_over19' => 'Grade12 / Over19y',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'guid' => 'required',
            'user_guid' => 'required|exists:users,guid',
            'sin' => 'required|numeric|digits:9',
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
            'guid' => Str::orderedUuid()->getHex(),
            'user_guid' => Auth::user()->guid,
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
