<?php

namespace App\Http\Requests;

use App\Models\Allocation;
use App\Models\Claim;
use App\Models\Student;
use App\Rules\InstitutionAllocationReached;
use App\Rules\RequiredDemographicsCompleted;
use App\Rules\ValidSin;
use Illuminate\Foundation\Http\FormRequest;

class ApplicationEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $claim = Claim::find($this->id);

        // Prevent updates if the current claim_status is not "Claimed" and the claim allocation is not active
        // This is to prevent updates to claims that are not in an active allocation
        // and are not in "Claimed" status
        if ($claim->claim_status !== 'Claimed' && $claim->allocation->status !== 'active') {
            return false;
        }
        // Check if the authenticated user has the necessary permissions to edit the institution.
        // You can access the authenticated user using the Auth facade or $this->user() method.
        return $this->user()->can('create', Claim::class);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'institution_guid.*' => 'The Institution field is required.',
            'program_guid.*' => 'The Program field is required.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $allocation = Allocation::where('guid', $this->input('allocation_guid'))->with('institution')->first();

        $rules = [
            'id' => 'required',
            'guid' => 'required',
            'claim_status' => 'required|string',
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
            'expiry_date' => 'required|date_format:Y-m-d',
            'correction_amount' => 'nullable|numeric',
            'correction_comment' => 'required_if:correction_amount,!null',
            'demographics' => 'nullable|array',
        ];

        if ($this->claim_status === 'Draft') {
            $rules = array_merge($rules, [
                'agreement_confirmed' => 'boolean',
                'registration_confirmed' => 'boolean',
            ]);

        } elseif ($this->claim_status === 'Submitted') {
            $rules = array_merge($rules, [
                'allocation_limit_reached' => new InstitutionAllocationReached($allocation),
                'student_guid' => ['required', 'exists:students,guid', new RequiredDemographicsCompleted($this->input('student_guid'))],

                'agreement_confirmed' => 'required|boolean|accepted:true',
                'registration_confirmed' => 'required|boolean|accepted:true',

                'registration_fee' => 'nullable|numeric',
                'materials_fee' => 'nullable|numeric',
                'program_fee' => 'nullable|numeric',
                'estimated_hold_amount' => 'required|numeric',
                'total_claim_amount' => 'nullable|numeric',
                'claim_percent' => 'required|numeric',

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

        $student = Student::where('user_guid', $this->user()->guid)->first();
        $allocation = Allocation::where('institution_guid', $this->institution_guid)
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->first();

        if ($student && $allocation) {

            $this->merge([
                'allocation_limit_reached' => null,
                'last_touch_by_user_guid' => $this->user()->guid,

                'allocation_guid' => $allocation->guid,
                'student_guid' => $student->guid,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'email' => $student->email,
                'zip_code' => $student->zip_code,
                'city' => $student->city,
                'sin' => $student->sin,
                'dob' => $student->dob,

                'agreement_confirmed' => $this->toBoolean($this->agreement_confirmed),
                'registration_confirmed' => $this->toBoolean($this->registration_confirmed),

                'registration_fee' => 0,
                'materials_fee' => 0,
                'program_fee' => 0,
                'estimated_hold_amount' => 0,
                'total_claim_amount' => 0,
                'claim_percent' => 0,
                'expiry_date' => $allocation->py->end_date,
            ]);
        } else {
            // Handle cases where $student or $allocation is null
            throw new \Exception('Student or Allocation not found.');
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
     * Handle a failed authorization attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function failedAuthorization(): void
    {
        throw new \Illuminate\Auth\Access\AuthorizationException(
            'Editing this application is not authorized. Make sure you are submitting an application for the current active year.'
        );
    }
}
