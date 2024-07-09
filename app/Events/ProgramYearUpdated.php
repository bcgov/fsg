<?php

namespace App\Events;

use App\Models\ProgramYear;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProgramYearUpdated
{
    use Dispatchable, SerializesModels;

    public $programYear;

    public $status;

    /**
     * Create a new event instance.
     */
    public function __construct(ProgramYear $programYear, $status)
    {
        $this->programYear = $programYear;
        $this->status = $status;
    }

}
