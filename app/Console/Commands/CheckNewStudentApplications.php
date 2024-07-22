<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckNewStudentApplications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-new-student-applications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for new student applications and update their status to pending';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \Log::info('app:check-new-student-applications');
    }
}
