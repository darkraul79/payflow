<?php

namespace App\Jobs;

use App\Models\Donation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessDonationPayment implements ShouldQueue
{
    use Queueable;

    private Donation $donation;

    /**
     * Create a new job instance.
     */
    public function __construct(Donation $donation)
    {
        //
        $this->donation = $donation;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $this->donation->processPay();
    }
}
