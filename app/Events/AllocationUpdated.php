<?php

namespace App\Events;

use App\Models\Allocation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AllocationUpdated
{
    use Dispatchable, SerializesModels;

    public $allocation;

    public $status;

    /**
     * Create a new event instance.
     */
    public function __construct(Allocation $allocation, $status)
    {
        $this->allocation = $allocation;
        $this->status = $status;
    }
}
