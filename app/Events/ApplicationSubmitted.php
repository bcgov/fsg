<?php

namespace App\Events;

use App\Models\Claim;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApplicationSubmitted
{
    use Dispatchable, SerializesModels;

    public $application;

    public $status;

    /**
     * Create a new event instance.
     */
    public function __construct(Claim $application, $status)
    {
        $this->application = $application;
        $this->status = $status;
    }
}
