<?php

namespace App\Http\Requests;

use App\Models\Program;
use Illuminate\Foundation\Http\FormRequest;

class ProgramEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $program = Program::find($this->id);

        return $this->user()->can('update', $program);
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
            'institution_guid' => 'required|exists:institutions,guid',
            'program_name' => 'required',
            'delivery_method' => 'required',
            'online_delivery_type' => 'required',
            'credential_type' => 'required',
            'high_priority_industry' => 'required',

            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d',

            'creditable' => 'required|boolean',
            'full_time' => 'required|boolean',
            'prov_funded_micro_cred' => 'required|boolean',
            'indigenous_related_learning' => 'required|boolean',
            'diversity_inclusion_related_learning' => 'required|boolean',
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
