<?php

namespace App\Listeners;

use App\Events\ClaimSubmitted;
use App\Models\Claim;
use App\Models\Program;
use App\Models\Student;
use Illuminate\Support\Facades\Log;

class ProcessSubmittedClaim
{
    /**
     * Handle the event.
     */
    public function handle(ClaimSubmitted $event): void
    {
        Log::info('handle institution claim submitted');

        // Get the cap, attestation and status from the event
        $claim_before_update = $event->claim;
        $status = $event->status;
        $claim = Claim::where('id', $claim_before_update->id)->with('allocation.py', 'student')->first();
        $program = Program::where('guid', $claim->program_guid)->first();
        $student = Student::where('guid', $claim->student_guid)->first();
        $claim->process_feedback = null;


//        Log::info($claim_before_update->claim_status . ' => ' . $status);

        $allowedToUpdateClaimFromInactivePy = false;
        // If the claim is to add outcome_effective_date and outcome_status
        // Then check $claim_before_update->outcome_effective_date and $claim_before_update->outcome_status
        // They should be null and $claim->outcome_effective_date and $claim->outcome_status should be set
        if($claim_before_update->outcome_effective_date === null && $claim_before_update->outcome_status === null
            && $claim->outcome_effective_date !== null && $claim->outcome_status !== null) {
            $allowedToUpdateClaimFromInactivePy = true;
        }

        if (!$allowedToUpdateClaimFromInactivePy && $claim->allocation->status != 'active' && $status != 'Cancelled') {
            $claim->claim_status = 'Draft';
        } else {
            $claim->claim_percent = $claim->allocation->py->claim_percent;

            // If the student is moving the claim from Draft to Submitted
            if ($claim_before_update->claim_status === 'Draft' && $status === 'Submitted') {
                Log::info('claim is moving from Draft to Submitted');
                $claim->estimated_hold_amount = 0;
                $claim->total_claim_amount = 0;
                $claim->program_fee = 0;
                $claim->materials_fee = 0;
                $claim->registration_fee = 0;

                // Calculate the total of claims for the student that are in Hold
                $totalHoldClaims = $student->claims()
                    ->where('claim_status', 'Hold')
                    ->select('estimated_hold_amount')
                    ->pluck('estimated_hold_amount')
                    ->sum();

                // Calculate the total of claims for the student excluding Draft, Hold, Expired, Cancelled
                $totalActiveClaims = $student->claims()
                    ->where('claim_status', 'Claimed')
                    ->sum(\DB::raw('COALESCE(program_fee, 0) + COALESCE(materials_fee, 0) + COALESCE(registration_fee, 0) + COALESCE(correction_amount, 0)'));

//                Log::info('totalHoldClaims = '.number_format($totalHoldClaims, 0));
//                Log::info('totalActiveClaims = '.number_format($totalActiveClaims, 0));

                // If the student has reached the grant limit, prevent moving it to Submitted
                if (((float) $totalHoldClaims + (float) $totalActiveClaims) > (float) env('TOTAL_GRANT')) {
                    $claim->process_feedback = 'Student has reached the total claim amount';
                    $claim->claim_status = 'Draft';
                }
            }

            // If the claim is Submitted and the inst. is moving to Hold
            elseif ($claim_before_update->claim_status === 'Submitted' && $status === 'Hold') {
//                Log::info('claim is moving from Submitted to Hold');

                // Calculate the total of claims for the student that are in Hold
                $totalHoldClaims = $student->claims()
                    ->where('claim_status', 'Hold')
                    ->select('estimated_hold_amount')
                    ->pluck('estimated_hold_amount')
                    ->sum();

                // Calculate the total of claims for the student excluding Draft, Hold, Expired, Cancelled
                $totalActiveClaims = $student->claims()
                    ->where('claim_status', 'Claimed')
                    ->sum(\DB::raw('COALESCE(program_fee, 0) + COALESCE(materials_fee, 0) + COALESCE(registration_fee, 0) + COALESCE(correction_amount, 0)'));

//                Log::info('totalHoldClaims = '.number_format($totalHoldClaims, 0));
//                Log::info('totalActiveClaims = '.number_format($totalActiveClaims, 0));

                // If the student has reached the grant limit, prevent moving it to Hold
                if (((float) $totalHoldClaims + (float) $totalActiveClaims) > (float) env('TOTAL_GRANT')) {
                    $claim->process_feedback = 'Student total claims of Hold and Claimed, including this,
                    is $'.(float) $totalHoldClaims + (float) $totalActiveClaims.' > $'.(float) env('TOTAL_GRANT').',
                    student currently has $' . (float) $totalHoldClaims + (float) $totalActiveClaims - (float)$claim->estimated_hold_amount . ' in claimed and held.';
                    $claim->claim_status = 'Submitted';
                    $claim->estimated_hold_amount = 0;
                }
            }

            // If the claim is in Hold and the inst. is just updating it to Hold
            elseif ($claim_before_update->claim_status === 'Hold' && $status === 'Hold') {
//                Log::info('claim is staying as Hold');

                // Calculate the total of claims for the student that are in Hold
                $totalHoldClaims = $student->claims()
                    ->where('claim_status', 'Hold')
                    ->select('estimated_hold_amount')
                    ->pluck('estimated_hold_amount')
                    ->sum();

                // Calculate the total of claims for the student excluding Draft, Hold, Expired, Cancelled
                $totalActiveClaims = $student->claims()
                    ->where('claim_status', 'Claimed')
                    ->sum(\DB::raw('COALESCE(program_fee, 0) + COALESCE(materials_fee, 0) + COALESCE(registration_fee, 0) + COALESCE(correction_amount, 0)'));

//                Log::info('totalHoldClaims = '.number_format($totalHoldClaims, 0));
//                Log::info('totalActiveClaims = '.number_format($totalActiveClaims, 0));

                // If the student has reached the grant limit, prevent moving it to Hold
                if (((float) $totalHoldClaims + (float) $totalActiveClaims) > (float) env('TOTAL_GRANT')) {
                    $claim->process_feedback = 'Student total claims of Hold and Claimed, including this,
                    is $'.(float) $totalHoldClaims + (float) $totalActiveClaims.' > $'.(float) env('TOTAL_GRANT').',
                    student currently has $' . (float) $totalHoldClaims + (float) $totalActiveClaims - (float)$claim->estimated_hold_amount . ' in claimed and held.';
                    $claim->claim_status = 'Submitted';
                    $claim->estimated_hold_amount = 0;
                }
            }

            // If the claim is moving from Hold to Claimed
            elseif ($claim_before_update->claim_status === 'Hold' && $status === 'Claimed') {

                Log::info('claim is moving from Hold to Claimed');

                $checkStatus = true;
                // Calculate sum claims of the institution that are not Draft, Cancelled or Expired
                // We need the sum of claims that are Claimed
                $sum_claims = Claim::where('claim_status', 'Claimed')
                        ->where('institution_guid', $claim->institution_guid)
                        ->where('allocation_guid', $claim->allocation_guid)
                        ->sum(\DB::raw('COALESCE(program_fee, 0) + COALESCE(materials_fee, 0) + COALESCE(registration_fee, 0) + COALESCE(correction_amount, 0)'));

//                Log::info('claims sum_claims = '.number_format($sum_claims, 0));
//                Log::info('allocation total_amount = '.number_format($claim->allocation->total_amount, 0));

                $total = (float) $sum_claims + ((float) $sum_claims / (float) $claim->py_admin_fee);
                Log::info('total + admin fee = '.number_format($total, 0));

                // Prevent inst. from switching to Claimed if the total of their claims is gte the allocation total
                // Make sure all calculation to include admin fee. Allocation total is inclusive of the admin fee
                if ($total > (float) $claim->allocation->total_amount) {
                    Log::info('total + admin fee = '.number_format($total, 0) . ' > allocation total_amount = '.number_format((float) $claim->allocation->total_amount, 0));
                    $claim->process_feedback = 'Institution has reached the total claim amount';
                    $claim->claim_status = 'Hold';
                    $claim->total_claim_amount = 0;
                    $claim->program_fee = 0;
                    $claim->materials_fee = 0;
                    $claim->registration_fee = 0;
                    $claim->correction_amount = 0;
                    $checkStatus = false;
                }

                // if the program of the claim is of type Transferable Skills, then we need to check the ts_percent
                if ($checkStatus && $program->funding_type === 'Transferable Skills') {
                    Log::info('claim is of type Transferable Skills');
                    // Calculate sum claims of the institution that are not Draft, Cancelled or Expired
                    // We need the sum of claims that are Claimed and claim.program are of type Transferable Skills
                    // exclude the current claim
                    $sum_ts_claims = Claim::where('claim_status', 'Claimed')
                            ->where('institution_guid', $claim->institution_guid)
                            ->where('allocation_guid', $claim->allocation_guid)
                            ->where('id', '!=', $claim->id)
                            ->whereHas('program', function($q) {
                                $q->where('funding_type', 'Transferable Skills');
                            })
                            ->sum(\DB::raw('COALESCE(program_fee, 0) + COALESCE(materials_fee, 0) + COALESCE(registration_fee, 0) + COALESCE(correction_amount, 0)'));

                    $ts_claims_total = (float) $sum_ts_claims + ((float) $sum_ts_claims / (float) $claim->py_admin_fee);

                    // Check if the total of the claim is greater than the ts_percent of the allocation
                    $tsPercent = (float) $claim->allocation->ts_percent;
                    Log::info('ts_claims_total = '.number_format($ts_claims_total, 0));

                    // If the total claim amount is greater than the ts_percent, then we need to set it to Hold
                    if ($ts_claims_total > ((float) $claim->allocation->total_amount * ($tsPercent / 100))) {

                        Log::info('ts_claims_total = '.number_format($ts_claims_total, 0) . ' > allocation total_amount * ts_percent = '.number_format((float) $claim->allocation->total_amount * ($tsPercent / 100), 0));
                        $claim->process_feedback = 'Claim exceeds the Transferable Skills percentage limit.';
                        $claim->claim_status = 'Hold';
                        $claim->total_claim_amount = 0;
                        $claim->program_fee = 0;
                        $claim->materials_fee = 0;
                        $claim->registration_fee = 0;
                        $claim->correction_amount = 0;
                        $checkStatus = false;
                    }
                }

                //check student
                // Calculate the total of claims for the student that are in Hold
//                $totalHoldClaims = $student->claims()
//                    ->where('claim_status', 'Hold')
//                    ->select('estimated_hold_amount')
//                    ->pluck('estimated_hold_amount')
//                    ->sum();

                // Calculate the total of claims for the student excluding Draft, Hold, Expired
                $totalActiveClaims = $student->claims()
                    ->where('claim_status', 'Claimed')
                    ->sum(\DB::raw('COALESCE(program_fee, 0) + COALESCE(materials_fee, 0) + COALESCE(registration_fee, 0) + COALESCE(correction_amount, 0)'));

                // If the student has reached the grant limit, prevent moving it to Submitted
                if ($checkStatus && (float) $totalActiveClaims > (float) env('TOTAL_GRANT')) {

//                    Log::info('totalActiveClaims = '.(float) $totalActiveClaims);
//                    Log::info('$claim->total_claim_amount = '.(float) $claim->total_claim_amount);
//                    Log::info('TOTAL_GRANT = '.(float) env('TOTAL_GRANT'));

                    $diff = (float) $totalActiveClaims - (float) env('TOTAL_GRANT');
                    $claim->process_feedback = 'Student has exceeded the grant total amount by $' . $diff;
                    $claim->claim_status = 'Hold';
                    $claim->total_claim_amount = 0;
                    $claim->program_fee = 0;
                    $claim->materials_fee = 0;
                    $claim->registration_fee = 0;
                    $claim->correction_amount = 0;
                    $checkStatus = false;
                }
            }

            // If the claim is moving to Cancelled/Expired
            elseif ($status === 'Cancelled' || $status === 'Expired') {

                Log::info('claim is moving to Cancelled/Expired');
                $claim->estimated_hold_amount = 0;
                $claim->total_claim_amount = 0;
                $claim->program_fee = 0;
                $claim->materials_fee = 0;
                $claim->registration_fee = 0;
                $claim->claim_percent = 0;
                $claim->correction_amount = 0;
            }
        }

        $claim->save();
    }
}
