<?php

namespace App\Http\Requests;

use App\Models\Program;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class ProgramStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Program::class);
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
            'institution_guid' => 'required|exists:institutions,guid',
            'program_name' => 'required',
            'delivery_method' => 'required',
            'online_delivery_type' => 'required',
            'credential_type' => 'required',
            'micro_credential_type' => 'nullable',
            'high_priority_industry' => 'required',

            'creditable' => 'required|boolean',
            'full_time' => 'required|boolean',
            'prov_funded_micro_cred' => 'required|boolean',
            'indigenous_related_learning' => 'required|boolean',
            'diversity_inclusion_related_learning' => 'required|boolean',
            'active_status' => 'required|boolean',

            'last_touch_by_user_guid' => 'required|exists:users,guid',

        ];
    }

    public function messages(): array
    {
        return [
            'institution_guid.required' => 'Institution is required',
            'program_name.required' => 'Program name is required',
            'delivery_method.required' => 'Delivery method is required',
            'online_delivery_type.required' => 'Online delivery method is required',
            'credential_type.required' => 'Credential type is required',
            'high_priority_industry.required' => 'High priority industry is required',
//            'start_date.required' => 'Start date is required',
//            'end_date.required' => 'End date is required',
            'creditable.required' => 'Creditable is required',
            'full_time.required' => 'Full time is required',

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

            'active_status' => $this->toBoolean($this->active_status),
            'creditable' => $this->toBoolean($this->creditable),
            'full_time' => $this->toBoolean($this->full_time),
            'prov_funded_micro_cred' => $this->toBoolean($this->prov_funded_micro_cred),
            'indigenous_related_learning' => $this->toBoolean($this->indigenous_related_learning),
            'diversity_inclusion_related_learning' => $this->toBoolean($this->diversity_inclusion_related_learning),

            'last_touch_by_user_guid' => $this->user()->guid,
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
