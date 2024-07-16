<?php

namespace App\Listeners;

use App\Events\AllocationUpdated;
use App\Events\ClaimSubmitted;
use App\Models\Allocation;
use App\Models\Claim;
use App\Models\Program;
use App\Models\Student;
use Illuminate\Support\Facades\Log;

class ProcessUpdatedAllocation
{

    /**
     * Handle the event.
     */
    public function handle(AllocationUpdated $event): void
    {
        // Get the cap, attestation and status from the event
        $allocation_before_update = $event->allocation;
        $status = $event->status;
        $allocation = Allocation::where('id', $allocation_before_update->id)->first();

        // If the claim submitted against an inactive allocation stop there.
        if($status === 'active'){
            Allocation::where('institution_guid', $allocation->institution_guid)
                ->whereNot('id', $allocation->id)
                ->update(['status' => 'inactive']);
        }
    }
}
