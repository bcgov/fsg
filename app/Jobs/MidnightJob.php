<?php

namespace App\Jobs;

use App\Events\ClaimSubmitted;
use App\Models\Claim;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MidnightJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::info('Midnight Job Started');
        $claims = Claim::where('claim_status', 'Hold')
            ->where('expiry_date', '<', Carbon::now())
            ->get();
        foreach ($claims as $claim) {
            $claim->update(['claim_status' => 'Expired']);
            $updatedClaim = Claim::where('id', $claim->id)->first();
            event(new ClaimSubmitted($updatedClaim, 'Expired'));
            \Log::info('Claim ID: '.$claim->id.' expired.');
        }

        \Log::info('Midnight Job Finished');

    }
}
