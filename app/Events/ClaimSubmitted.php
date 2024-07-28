<?php

namespace App\Events;

use App\Models\Claim;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClaimSubmitted
{
    use Dispatchable, SerializesModels;

    public $claim;

    public $status;

    /**
     * Create a new event instance.
     */
    public function __construct(Claim $claim, $status)
    {
        $this->claim = $claim;
        $this->status = $status;
    }
}
