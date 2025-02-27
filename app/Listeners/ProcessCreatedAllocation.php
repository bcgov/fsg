<?php

namespace App\Listeners;

use App\Events\AllocationCreated;
use App\Models\Allocation;

class ProcessCreatedAllocation
{
    /**
     * Handle the event.
     */
    public function handle(AllocationCreated $event): void
    {
        // Get the cap, attestation and status from the event
//        $allocation_before_create = $event->allocation;
//        $allocation = Allocation::where('id', $allocation_before_create->id)->first();
//
//        Allocation::where('institution_guid', $allocation->institution_guid)
//            ->whereNot('id', $allocation->id)
//            ->update(['status' => 'inactive']);
    }
}
