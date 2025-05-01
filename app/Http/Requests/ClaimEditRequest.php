<?php

namespace App\Http\Requests;

use App\Models\Allocation;
use App\Models\Claim;
use App\Rules\InstitutionAllocationReached;
use App\Rules\ValidSin;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

//        Log::info("ClaimEditRequest - Claim Status: $claim->claim_status");

        if (! $claim) {
            return false; // Claim not found
        }

        // Prevent updates if the current claim_status is "Claimed"
        if ($claim->claim_status === 'Claimed' && $claim->outcome_effective_date != null && $claim->outcome_status != null) {
            return false;
        }

        // Prevent updates if the current claim_status is "Claimed" and already got correction
        if ($claim->claim_status === 'Claimed' && $claim->correction != null) {
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
            'dob.*' => 'The Date of Birth field is invalid.',
            'estimated_hold_amount.*' => 'The Est. Hold Amount field is invalid.',
            'stable_enrolment_date.*' => 'The Actual Stable Enrol. Date field is invalid.',
            'expected_stable_enrolment_date.*' => 'The Expected Stable Enrol. Date field is invalid.',
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
            'stable_enrolment_date' => 'nullable|date_format:Y-m-d|after:2020-01-20',
            'expected_stable_enrolment_date' => 'nullable|date_format:Y-m-d|after:2020-01-20',
            'expected_completion_date' => 'nullable|date_format:Y-m-d|after:2020-01-20',
            'outcome_effective_date' => 'nullable|date_format:Y-m-d|after:2020-01-20',
            'outcome_status' => 'nullable|string',
            'correction_amount' => 'nullable|numeric',
            'correction_comment' => 'required_if:correction_amount,!null',

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
                'stable_enrolment_date' => 'nullable|date_format:Y-m-d|after:2020-01-20',
                'expected_stable_enrolment_date' => 'nullable|date_format:Y-m-d|after:2020-01-20',
                'expiry_date' => 'nullable|date_format:Y-m-d|after:2020-01-20',

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
                'stable_enrolment_date' => 'nullable|date_format:Y-m-d|after:2020-01-20',
                'expected_stable_enrolment_date' => 'required|date_format:Y-m-d|after:2020-01-20',
                'expiry_date' => 'required|date_format:Y-m-d|after:2020-01-20',

                'fifty_two_week_affirmation' => 'required|boolean|in:true,1',
                'agreement_confirmed' => 'required|boolean|in:true,1',
                'registration_confirmed' => 'required|boolean|in:true,1',

            ]);
        } elseif ($this->claim_status === 'Claimed') {

            $allocation = Allocation::where('guid', $this->input('allocation_guid'))->with('institution')->first();

            $rules = array_merge($rules, [
                'allocation_limit_reached' => ['required', new InstitutionAllocationReached($allocation)],
                'registration_fee' => 'required|numeric',
                'materials_fee' => 'required|numeric',
                'program_fee' => 'required|numeric',
                'estimated_hold_amount' => 'required|numeric',
                'total_claim_amount' => 'required|numeric',
                'claim_percent' => 'required|numeric',
                'stable_enrolment_date' => 'required|date_format:Y-m-d|after:2020-01-20',
                'expected_stable_enrolment_date' => 'required|date_format:Y-m-d|after:2020-01-20',
                'expiry_date' => 'required|date_format:Y-m-d|after:2020-01-20',

                'fifty_two_week_affirmation' => 'required|boolean|in:true,1',
                'agreement_confirmed' => 'required|boolean|in:true,1',
                'registration_confirmed' => 'required|boolean|in:true,1',

                'psi_claim_request_date' => 'nullable|date_format:Y-m-d|after:2020-01-20',
                'reporting_completed_date' => 'nullable|date_format:Y-m-d|after:2020-01-20',
                'claimed_date' => 'required|date_format:Y-m-d|after:2020-01-20',
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
        $correction = $this->sanitizeAndConvertToFloat($this->input('correction_amount'));

        $this->merge([
            'agreement_confirmed' => $this->toBoolean($this->agreement_confirmed),
            'registration_confirmed' => $this->toBoolean($this->registration_confirmed),
        ]);

        if ($this->claim_status === 'Draft') {
            $this->merge([
                'first_name' => Str::title($this->first_name),
                'last_name' => Str::title($this->last_name),
                'email' => Str::lower(str_replace(' ', '', $this->email)),
                'zip_code' => Str::upper(str_replace(' ', '', $this->zip_code)),
                'city' => Str::title($this->city),
            ]);
        } elseif ($this->claim_status === 'Submitted') {
            $this->merge([
                'fifty_two_week_affirmation' => $this->toBoolean($this->fifty_two_week_affirmation),
            ]);
        } elseif ($this->claim_status === 'Hold') {
            $this->merge([
                'fifty_two_week_affirmation' => $this->toBoolean($this->fifty_two_week_affirmation),
            ]);
        } elseif ($this->claim_status === 'Claimed') {

            $today = Carbon::now()->startOfDay()->format('Y-m-d');

            // Calculate the total
            $total = $registrationFee + $materialsFee + $programFee + $correction;

            $this->merge([
                'total_claim_amount' => $total,
                'claimed_date' => $today,
                'claimed_by_user_guid' => $this->user()->guid,
                'allocation_limit_reached' => true,
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
     * @param  mixed  $value
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
