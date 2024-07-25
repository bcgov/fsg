<?php

namespace App\Http\Requests;

use App\Models\Claim;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use App\Rules\ValidSin;

class ClaimEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $claim = Claim::find($this->id);

        if (!$claim) {
            return false; // Claim not found
        }

        // Prevent updates if the current claim_status is "Claimed"
        if ($claim->claim_status === 'Claimed' && $claim->outcome_effective_date != null && $claim->outcome_status != null) {
            return false;
        }

        // Allow switching to Cancelled only if claim is in Hold status and stable enrol. date is yet to come
//        if ($this->claim_status === 'Cancelled' && $claim->claim_status !== 'Hold') {
//            return false;
//        }
//        if ($this->claim_status === 'Cancelled' && $claim->claim_status === 'Hold') {
            // Robyn said remove this.
//            if($claim->stable_enrolment_date < Carbon::now()) {
//                return false;
//            }
//        }
        if ($this->claim_status === 'Cancelled' && ($claim->claim_status == 'Draft' || $claim->claim_status == 'Expired')) {
            return false;
        }


        // Check if the authenticated user has the necessary permissions to edit the institution.
        // You can access the authenticated user using the Auth facade or $this->user() method.
        return $this->user()->can('update', $claim);
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
        $rules = [
            'id' => 'required',
            'guid' => 'required',
            'program_guid' => 'required|exists:programs,guid',
            'claim_status' => 'required|string',
            'stable_enrolment_date' => 'nullable',
            'expected_stable_enrolment_date' => 'nullable',
            'expected_completion_date' => 'nullable',
            'outcome_effective_date' => 'nullable',
            'outcome_status' => 'nullable',
            ];

        // If the status is "Cancelled" or 'Expired', do not validate other fields
        if ($this->claim_status === 'Cancelled' || $this->claim_status === 'Expired') {
            return $rules;
        }

        if ($this->claim_status === 'Draft') {
            $rules = array_merge($rules, [
                'institution_guid' => 'required|exists:institutions,guid',
                'allocation_guid' => 'required|exists:allocations,guid',
                'program_guid' => 'required|exists:programs,guid',
                'student_guid' => 'required|exists:students,guid',

                'sin' => ['required', new ValidSin],
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'dob' => 'required|date_format:Y-m-d',
                'email' => 'required|email',
                'city' => 'required|string',
                'zip_code' => 'required|string|regex:/^[A-Za-z]\d[A-Za-z]\d[A-Za-z]\d$/',
                'agreement_confirmed' => 'required|boolean',
                'registration_confirmed' => 'required|boolean',
            ]);
        } elseif ($this->claim_status === 'Submitted') {
            $rules = array_merge($rules, [
                'registration_fee' => 'nullable|numeric',
                'materials_fee' => 'nullable|numeric',
                'program_fee' => 'nullable|numeric',
                'estimated_hold_amount' => 'required|numeric|gte:0',
                'total_claim_amount' => 'nullable|numeric',
                'claim_percent' => 'required|numeric',
                'stable_enrolment_date' => 'nullable|date_format:Y-m-d',
                'expected_stable_enrolment_date' => 'nullable|date_format:Y-m-d',
                'expiry_date' => 'nullable|date_format:Y-m-d',

                'fifty_two_week_affirmation' => 'required|boolean|in:true,1',
                'agreement_confirmed' => 'required|boolean|in:true,1',
                'registration_confirmed' => 'required|boolean|in:true,1',

            ]);
        } elseif ($this->claim_status === 'Hold') {
            $rules = array_merge($rules, [
                'registration_fee' => 'nullable|numeric',
                'materials_fee' => 'nullable|numeric',
                'program_fee' => 'nullable|numeric',
                'estimated_hold_amount' => 'required|numeric|gte:0',
                'total_claim_amount' => 'nullable|numeric',
                'claim_percent' => 'required|numeric',
                'stable_enrolment_date' => 'nullable|date_format:Y-m-d',
                'expected_stable_enrolment_date' => 'nullable|date_format:Y-m-d',
                'expiry_date' => 'required|date_format:Y-m-d',

                'fifty_two_week_affirmation' => 'required|boolean|in:true,1',
                'agreement_confirmed' => 'required|boolean|in:true,1',
                'registration_confirmed' => 'required|boolean|in:true,1',

            ]);
        } elseif ($this->claim_status === 'Claimed') {
            $rules = array_merge($rules, [
                'registration_fee' => 'required|numeric',
                'materials_fee' => 'required|numeric',
                'program_fee' => 'required|numeric',
                'estimated_hold_amount' => 'required|numeric',
                'total_claim_amount' => 'required|numeric',
                'claim_percent' => 'required|numeric',
                'stable_enrolment_date' => 'required|date_format:Y-m-d',
                'expected_stable_enrolment_date' => 'required|date_format:Y-m-d',
                'expiry_date' => 'required|date_format:Y-m-d',

                'fifty_two_week_affirmation' => 'required|boolean|in:true,1',
                'agreement_confirmed' => 'required|boolean|in:true,1',
                'registration_confirmed' => 'required|boolean|in:true,1',

                'psi_claim_request_date' => 'nullable|date_format:Y-m-d',
                'reporting_completed_date' => 'nullable|date_format:Y-m-d',
                'claimed_date' => 'required|date_format:Y-m-d',
                'claimed_by_user_guid' => 'required|exists:users,guid',
            ]);
        }

        return $rules;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // If the status is "Cancelled", do not modify any other fields
        if ($this->claim_status === 'Cancelled' || $this->claim_status === 'Expired') {
            return;
        }

        // Sanitize and cast the fee fields to numeric values
        $registrationFee = $this->sanitizeAndConvertToFloat($this->input('registration_fee'));
        $materialsFee = $this->sanitizeAndConvertToFloat($this->input('materials_fee'));
        $programFee = $this->sanitizeAndConvertToFloat($this->input('program_fee'));

        if ($this->claim_status === 'Draft') {
            $this->merge([
                'first_name' => Str::title(str_replace(' ', '', $this->first_name)),
                'last_name' => Str::title(str_replace(' ', '', $this->last_name)),
                'email' => Str::lower(str_replace(' ', '', $this->email)),
                'zip_code' => Str::upper(str_replace(' ', '', $this->zip_code)),
                'city' => Str::title(str_replace(' ', '', $this->city)),

                'agreement_confirmed' => $this->toBoolean($this->agreement_confirmed),
            ]);
        } elseif ($this->claim_status === 'Submitted' || $this->claim_status === 'Hold') {
            $this->merge([
                'fifty_two_week_affirmation' => $this->toBoolean($this->fifty_two_week_affirmation),
            ]);
        } elseif ($this->claim_status === 'Claimed') {

            $today = Carbon::now()->startOfDay()->format('Y-m-d');

            // Calculate the total
            $total = $registrationFee + $materialsFee + $programFee;

            $this->merge([
                'total_claim_amount' => $total,
                'claimed_date' => $today,
                'claimed_by_user_guid' => $this->user()->guid,
            ]);
        }
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

    /**
     * Sanitize and convert a value to float.
     *
     * @param mixed $value
     * @return float
     */
    protected function sanitizeAndConvertToFloat($value)
    {
        // Remove any non-numeric characters (except dot)
        $value = preg_replace('/[^0-9.]/', '', $value);

        // Convert to float
        return (float) $value;
    }
}
