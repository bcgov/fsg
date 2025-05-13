<?php

namespace App\Http\Requests;

use App\Models\Student;
use App\Rules\ValidSin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
//use Illuminate\Contracts\Validation\Validator;
//use Illuminate\Http\Exceptions\HttpResponseException;
//use Illuminate\Support\Facades\Log;

class StudentEditRequest extends FormRequest
{
//    protected function failedValidation(Validator $validator)
//    {
//        // Log the validation errors
//        Log::error('Validation failed in ' . static::class, $validator->errors()->toArray());
//
//        // Then throw the HttpResponseException as usual
//        throw new HttpResponseException(
//            response()->json([
//                'status' => false,
//                'errors' => $validator->errors(),
//            ], 422)
//        );
//    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $student = Student::find($this->id);

        return $this->user()->can('update', $student);
    }

    public function messages(): array
    {
        return [
            'id.required' => 'The ID field is required.',
            'guid.required' => 'The GUID field is required.',
            'user_guid.required' => 'The User GUID field is required.',
            'user_guid.exists' => 'The selected User GUID is invalid.',
            'sin.required' => 'The SIN field is required.',
            'first_name.required' => 'The First Name field is required.',
            'last_name.required' => 'The Last Name field is required.',
            'dob.required' => 'The Date of Birth field is required.',
            'dob.date_format' => 'The Date of Birth must be in the format YYYY-MM-DD.',
            'email.required' => 'The Email field is required.',
            'email.email' => 'The Email must be a valid email address.',
            'zip_code.required' => 'The Postal Code field is required.',
            'zip_code.regex' => 'The Postal Code must be a valid Canadian postal code in the format A1A1A1.',
            'citizenship.required' => 'The Citizenship field is required.',
            'grade12_or_over19.required' => 'The Grade 12 or Over 19 field is required.',
            'info_consent.boolean' => 'The Info Consent field must be true.',
            'info_consent.accepted' => 'The Info Consent field must be true.',
            'duplicative_funding.boolean' => 'The Duplicative Funding field must be true.',
            'duplicative_funding.accepted' => 'The Duplicative Funding field must be true.',
            'tax_implications.boolean' => 'The Tax Implications field must be true.',
            'tax_implications.accepted' => 'The Tax Implications field must be true.',
            'lifetime_max.boolean' => 'The Lifetime Max field must be true.',
            'lifetime_max.accepted' => 'The Lifetime Max field must be true.',
            'fed_prov_benefits.boolean' => 'The Federal/Provincial Benefits field must be true.',
            'fed_prov_benefits.accepted' => 'The Federal/Provincial Benefits field must be true.',
            'workbc_client.boolean' => 'The WorkBC Client field must be true.',
            'workbc_client.accepted' => 'The WorkBC Client field must be true.',
            'additional_supports.boolean' => 'The Additional Supports field must be true.',
            'additional_supports.accepted' => 'The Additional Supports field must be true.',
            'bc_resident.boolean' => 'The BC Resident field must be true.',
            'bc_resident.accepted' => 'The BC Resident field must be true.',
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
            'id' => 'required',
            'guid' => 'required',
            'user_guid' => 'required|exists:users,guid',
            'sin' => ['required', new ValidSin],
            'first_name' => 'required',
            'last_name' => 'required',
            'dob' => 'required|date_format:Y-m-d',
            'email' => 'required|email',
            'city' => 'required',
            'zip_code' => 'required|string|regex:/^[A-Za-z]\d[A-Za-z]\d[A-Za-z]\d$/',

            'citizenship' => 'required',
            'grade12_or_over19' => 'required',
            'gender' => 'nullable',

            'info_consent' => 'boolean|accepted:true',
            'duplicative_funding' => 'boolean|accepted:true',
            'tax_implications' => 'boolean|accepted:true',
            'lifetime_max' => 'boolean|accepted:true',
            'fed_prov_benefits' => 'boolean|accepted:true',
            'workbc_client' => 'boolean|accepted:true',
            'additional_supports' => 'boolean|accepted:true',
            'bc_resident' => 'boolean|accepted:true',
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
            'city' => Str::title($this->city),
            'first_name' => Str::title($this->first_name),
            'last_name' => Str::title($this->last_name),

            'info_consent' => $this->toBoolean($this->info_consent),
            'duplicative_funding' => $this->toBoolean($this->duplicative_funding),
            'tax_implications' => $this->toBoolean($this->tax_implications),
            'lifetime_max' => $this->toBoolean($this->lifetime_max),
            'fed_prov_benefits' => $this->toBoolean($this->fed_prov_benefits),
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
