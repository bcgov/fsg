<?php

namespace App\Listeners;

use App\Events\ProgramYearUpdated;
use App\Models\Allocation;
use App\Models\ProgramYear;

class ProcessUpdatedProgramYear
{
    /**
     * Handle the event.
     * This will update the status of all allocations under the modified program year to match the program year's status.
     * If the modified record status is set to active then all other program years and associated allocations would be switched to inactive.
     */
    public function handle(ProgramYearUpdated $event): void
    {
        // Update the status of all allocations associated with this program year to match the PY status
        $programYearId = $event->programYear->id;
        $status = $event->status;
        $programYear = ProgramYear::with('allocations')->find($programYearId);

        if ($programYear) {
            // Update the status of the program year's allocations
            foreach ($programYear->allocations as $allocation) {
                $allocation->status = $status;
                $allocation->save();
            }

            // Deactivate other program years if the status is active
            if ($status === 'active') {
                // Deactivate all other program years
                ProgramYear::where('id', '!=', $programYearId)->update(['status' => 'inactive']);

                // Get IDs of allocations associated with the old program years
                $allocationIds = Allocation::whereHas('py', function ($query) use ($programYearId) {
                    $query->where('id', '!=', $programYearId);
                })->pluck('id');

                // Batch update the status of the allocations
                Allocation::whereIn('id', $allocationIds)->update(['status' => 'inactive']);
            }
        }
    }
}
