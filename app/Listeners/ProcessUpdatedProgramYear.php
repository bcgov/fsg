<?php

namespace App\Listeners;

use App\Events\ProgramYearUpdated;
use App\Models\Allocation;
use App\Models\Claim;
use App\Models\Program;
use App\Models\ProgramYear;
use App\Models\Student;
use Illuminate\Support\Facades\Log;

class ProcessUpdatedProgramYear
{

    /**
     * Handle the event.
     */
    public function handle(ProgramYearUpdated $event): void
    {
        // Update the status of all allocations associated with this program year to match the PY status
        $programYearId = $event->programYear;
        $status = $event->status;
        $programYear = ProgramYear::where('id', $programYearId->id)->with('allocations')->first();


        \Log::info('Start handling program year update to status: ' . $status);

        if (!is_null($programYear)) {
            \Log::info('start_date: '.$programYear->start_date);
            foreach ($programYear->allocations as $allocation) {
                \Log::info('allocation: '.$allocation->id);
                $allocation->status = $status;
                $allocation->save();
            }
        }
    }
}
