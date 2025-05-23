<?php

namespace App\Listeners;

use App\Events\ApplicationSubmitted;
use App\Models\Claim;
use App\Models\Program;
use App\Models\Student;
use Illuminate\Support\Facades\Log;

class ProcessSubmittedApplication
{
    /**
     * Handle the event.
     */
    public function handle(ApplicationSubmitted $event): void
    {
        \Log::info('handle student ProcessSubmittedApplication');

        // Get the cap, attestation and status from the event
        $claim_before_update = $event->application;
        $status = $event->status;
        $claim = Claim::where('id', $claim_before_update->id)->with('allocation.py', 'student')->first();
        $program = Program::where('guid', $claim->program_guid)->first();
        $student = Student::where('guid', $claim->student_guid)->first();
        $claim->process_feedback = null;

        // If the claim submitted against an inactive allocation stop there.
        if ($claim->allocation->status != 'active') {
            $claim->claim_status = 'Draft';
            $claim->process_feedback = 'Claim submitted against an inactive allocation';
        } else {

            // If the claim is new, add the claim percent from ProgramYear
            $claim->claim_percent = $claim->allocation->py->claim_percent;

            // If the student is moving the claim from Draft to Submitted
            if ($claim_before_update->claim_status === 'Draft' && $status === 'Submitted') {

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

                // If the student has reached the grant limit, prevent moving it to Submitted
                if (((float) $totalHoldClaims + (float) $totalActiveClaims) > (float) env('TOTAL_GRANT')) {
                    $claim->process_feedback = 'Student has reached the total claim amount';
                    $claim->claim_status = 'Draft';
                }
            }

            // If the claim is Submitted and the inst. is moving to Hold
            elseif ($claim_before_update->claim_status === 'Submitted' && $status === 'Hold') {

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

                // If the student has reached the grant limit, prevent moving it to Submitted
                if (((float) $totalHoldClaims + (float) $totalActiveClaims) > (float) env('TOTAL_GRANT')) {
                    $claim->process_feedback = 'Student has reached the total claim amount';
                    $claim->claim_status = 'Submitted';
                }

            }

            // If the claim is moving from Hold to Claimed
            elseif ($claim_before_update->claim_status === 'Hold' && $status === 'Claimed') {

                Log::info('claim is moving from Hold to Claimed');

                // Calculate sum claims of the institution that are not Draft, Cancelled or Expired
                $sum_claims = Claim::

                    // We need the sum of claims that are in Hold, Submitted or Claimed
                    whereNotIn('claim_status', ['Draft', 'Expired', 'Cancelled'])
                        ->where('institution_guid', $claim->institution_guid)
                        ->where('allocation_guid', $claim->allocation_guid)
                        ->sum(\DB::raw('COALESCE(program_fee, 0) + COALESCE(materials_fee, 0) + COALESCE(registration_fee, 0) + COALESCE(correction_amount, 0)'));

                Log::info('sum_claims = '.number_format($sum_claims, 0));
                Log::info('total_amount = '.number_format($claim->allocation->total_amount, 0));

                // Prevent inst. from switching to Claimed if the total of their claims is gte the allocation total
                if ((float) $sum_claims > (float) $claim->allocation->total_amount) {
                    $claim->process_feedback = 'Institution has reached the total claim amount';
                    $claim->claim_status = 'Hold';
                    $claim->total_claim_amount = 0;
                }

                //check student
                // Calculate the total of claims for the student that are in Hold
                $totalHoldClaims = $student->claims()
                    ->where('claim_status', 'Hold')
                    ->select('estimated_hold_amount')
                    ->pluck('estimated_hold_amount')
                    ->sum();

                // Calculate the total of claims for the student excluding Draft, Hold, Expired
                $totalActiveClaims = $student->claims()
                    ->where('claim_status', 'Claimed')
                    ->sum(\DB::raw('COALESCE(program_fee, 0) + COALESCE(materials_fee, 0) + COALESCE(registration_fee, 0) + COALESCE(correction_amount, 0)'));

                // If the student has reached the grant limit, prevent moving it to Submitted
                if (((float) $totalHoldClaims + (float) $totalActiveClaims) > (float) env('TOTAL_GRANT')) {
                    $claim->process_feedback = 'Student has exceeded the grant total amount';
                    $claim->claim_status = 'Hold';
                    $claim->total_claim_amount = 0;
                }
            }
        }

        $claim->save();
    }
}
