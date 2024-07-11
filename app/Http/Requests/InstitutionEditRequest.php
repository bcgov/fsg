<?php

namespace App\Http\Requests;

use App\Models\Institution;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class InstitutionEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $institution = Institution::find($this->id);

        // Check if the authenticated user has the necessary permissions to edit the institution.
        // You can access the authenticated user using the Auth facade or $this->user() method.
        return $this->user()->can('update', $institution);
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

            'name' => 'required|unique:institutions,name,'.$this->id,
            'legal_name' => 'nullable',

            'address1' => 'required',
            'address2' => 'nullable',
            'category' => 'nullable|exists:utils,field_name',

            'primary_contact' => 'required',
            'primary_email' => 'required',
            'city' => 'required',
            'postal_code' => 'required',

            'province' => 'required',
            'economic_region' => 'nullable',
            'size' => 'nullable',
            'active_status' => 'required|boolean',

            'last_touch_by_user_guid' => 'required|exists:users,guid',
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
            'active_status' => $this->toBoolean($this->active_status),
            'last_touch_by_user_guid' => $this->user()->guid,
            'postal_code' => Str::upper(str_replace(' ', '', $this->postal_code)),
            'primary_email' => Str::lower(str_replace(' ', '', $this->primary_email)),
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
