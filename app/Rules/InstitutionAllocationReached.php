<?php

namespace App\Rules;

use App\Models\Claim;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class InstitutionAllocationReached implements ValidationRule
{
    protected $allocation;

    public function __construct($allocation)
    {
        $this->allocation = $allocation;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Only allow the student to submit an application if the allocation limit has not been reached.

        // We need the sum of claims that are in Hold, Submitted or Claimed
        $sum_claims = Claim::whereIn('claim_status', ['Submitted', 'Claimed'])
            ->where('institution_guid', $this->allocation->institution->guid)
            ->where('allocation_guid', $this->allocation->guid)
            ->sum(\DB::raw('COALESCE(program_fee, 0) + COALESCE(materials_fee, 0) + COALESCE(registration_fee, 0) + COALESCE(correction_amount, 0)'));
        $sum_hold_claims = Claim::where('claim_status', 'Hold')
            ->where('institution_guid', $this->allocation->institution->guid)
            ->where('allocation_guid', $this->allocation->guid)
            ->sum('estimated_hold_amount');

        if ((float) $sum_claims + (float) $sum_hold_claims >= (float) $this->allocation->total_amount) {
            $fail('The institution has reached its allocation limit. Please contact the institution administrator.'
            . (float) $sum_claims . "+" . (float) $sum_hold_claims . ">=" . (float) $this->allocation->total_amount);
        }
    }
}
